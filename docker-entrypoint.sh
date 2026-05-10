#!/bin/sh

# ── Ensure writable directories exist ──
mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# ── Storage symlink ──
ln -sfn /app/storage/app/public /app/public/storage

# ── Clear compiled caches (safe on every deploy) ──
rm -f bootstrap/cache/*.php

# ── Copy .env.example → .env if no .env exists yet ──
if [ ! -f .env ]; then
    cp .env.example .env
    # Force skip install wizard in Docker — setup is handled by this entrypoint
    sed -i 's/^SKIP_INSTALL_WIZARD=.*/SKIP_INSTALL_WIZARD=true/' .env
    echo "[entrypoint] Created .env from .env.example (SKIP_INSTALL_WIZARD=true)"
fi

# ── Generate APP_KEY if not set ──
if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=.+' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
    echo "[entrypoint] Generated APP_KEY"
fi

# ── Run migrations (idempotent — safe on redeploy) ──
echo "[entrypoint] Running migrations..."

# Detect existing database without migrations table (e.g. set up via install wizard).
# Uses raw PHP/PDO to avoid artisan tinker issues.
php -r "
    require __DIR__.'/vendor/autoload.php';
    \$app = require_once __DIR__.'/bootstrap/app.php';
    \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    try {
        \$hasUsers = \Illuminate\Support\Facades\Schema::hasTable('users');
        \$hasMigrations = \Illuminate\Support\Facades\Schema::hasTable('migrations');

        if (\$hasUsers && !\$hasMigrations) {
            echo \"[entrypoint] Existing DB without migrations table detected\n\";

            // Create the migrations table
            \Illuminate\Support\Facades\Artisan::call('migrate:install');
            echo \"[entrypoint] Created migrations table\n\";

            // Seed all current migration files as already run
            \$files = glob(__DIR__.'/database/migrations/*.php');
            foreach (\$files as \$file) {
                \$name = basename(\$file, '.php');
                \Illuminate\Support\Facades\DB::table('migrations')->insert([
                    'migration' => \$name,
                    'batch' => 1,
                ]);
            }
            echo '[entrypoint] Seeded ' . count(\$files) . \" migration records\n\";
        }
    } catch (\Throwable \$e) {
        echo '[entrypoint] DB detection error: ' . \$e->getMessage() . \"\n\";
    }
" 2>&1

php artisan migrate --force --no-interaction || echo "[entrypoint] WARNING: migrations failed — continuing startup"

# ── First-time install: create admin + mark installed ──
INSTALLED_FLAG="storage/app/private/installed.json"
if [ ! -f "$INSTALLED_FLAG" ]; then
    echo "[entrypoint] First deploy detected — running initial setup..."

    # Create admin user if env vars are provided
    if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
        php artisan admin:create "$ADMIN_EMAIL" \
            --first="${ADMIN_FIRST_NAME:-Admin}" \
            --last="${ADMIN_LAST_NAME:-User}" \
            --password="$ADMIN_PASSWORD" \
            --verify
        echo "[entrypoint] Admin user created: $ADMIN_EMAIL"
    else
        echo "[entrypoint] WARNING: ADMIN_EMAIL and ADMIN_PASSWORD not set — skipping admin creation."
        echo "[entrypoint] You can create an admin later with: php artisan admin:create <email>"
    fi

    # Create storage link
    php artisan storage:link 2>/dev/null || true

    # Mark as installed so the wizard is skipped
    mkdir -p storage/app/private
    echo "{\"installed_at\":\"$(date -u +%Y-%m-%dT%H:%M:%S+00:00)\",\"method\":\"docker\"}" > "$INSTALLED_FLAG"
    echo "[entrypoint] Marked as installed"
fi

# ── Cache config/routes/views for production ──
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache --no-interaction 2>/dev/null || true
    php artisan route:cache --no-interaction 2>/dev/null || true
    php artisan view:cache --no-interaction 2>/dev/null || true
    echo "[entrypoint] Production caches warmed"
fi

# ── Start cron daemon (for schedule:run every minute) ──
echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" | crontab -
cron 2>/dev/null &
echo "[entrypoint] Cron started"

# ── Start queue worker in background ──
php artisan queue:work --queue=campaigns,email-validation,default \
    --sleep=3 --tries=3 --timeout=300 --max-jobs=1000 --max-time=3600 &
echo "[entrypoint] Queue worker started"

# ── Ensure public/index.php exists (app uses root index.php) ──
if [ ! -f public/index.php ] && [ -f index.php ]; then
    ln -sfn /app/index.php public/index.php
fi

# ── Start PHP built-in server ──
echo "[entrypoint] Starting web server on port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} server.php
