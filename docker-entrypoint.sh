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
    sed -i 's/^SKIP_INSTALL_WIZARD=.*/SKIP_INSTALL_WIZARD=true/' .env
    echo "[entrypoint] Created .env from .env.example (SKIP_INSTALL_WIZARD=true)"
fi

# ── Generate APP_KEY if not set ──
if [ -z "$APP_KEY" ] && ! grep -qE '^APP_KEY=.+' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
    echo "[entrypoint] Generated APP_KEY"
fi

# ── Ensure public/index.php exists (app uses root index.php) ──
if [ ! -f public/index.php ] && [ -f index.php ]; then
    cat > public/index.php <<'PHPEOF'
<?php
chdir(dirname(__DIR__));
require __DIR__ . '/../index.php';
PHPEOF
    echo "[entrypoint] Created public/index.php wrapper"
fi

# ── Run migrations ──
echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction 2>&1 || {
    echo "[entrypoint] Migrate failed — attempting fresh migration..."
    php artisan migrate:fresh --force --no-interaction 2>&1 || echo "[entrypoint] WARNING: migrate:fresh also failed"
}

# ── First-time install: create admin + mark installed ──
# Remove stale installed flag from previous broken deploy (one-time fix)
rm -f storage/app/private/installed.json

INSTALLED_FLAG="storage/app/private/installed.json"
if [ ! -f "$INSTALLED_FLAG" ]; then
    echo "[entrypoint] First deploy detected — running initial setup..."

    if [ -n "$ADMIN_EMAIL" ] && [ -n "$ADMIN_PASSWORD" ]; then
        php artisan admin:create "$ADMIN_EMAIL" \
            --first="${ADMIN_FIRST_NAME:-Admin}" \
            --last="${ADMIN_LAST_NAME:-User}" \
            --password="$ADMIN_PASSWORD" \
            --verify || echo "[entrypoint] WARNING: admin creation had errors — continuing"
        echo "[entrypoint] Admin user created: $ADMIN_EMAIL"
    else
        echo "[entrypoint] WARNING: ADMIN_EMAIL and ADMIN_PASSWORD not set — skipping admin creation."
        echo "[entrypoint] You can create an admin later with: php artisan admin:create <email>"
    fi

    php artisan storage:link 2>/dev/null || true

    mkdir -p storage/app/private
    echo "{\"installed_at\":\"$(date -u +%Y-%m-%dT%H:%M:%S+00:00)\",\"method\":\"docker\"}" > "$INSTALLED_FLAG"
    echo "[entrypoint] Marked as installed"
fi

# ── Clear all caches to ensure runtime env vars are used ──
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true
echo "[entrypoint] Caches cleared"

# ── Start cron daemon (for schedule:run every minute) ──
echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" | crontab -
cron 2>/dev/null &
echo "[entrypoint] Cron started"

# ── Start queue worker in background ──
php artisan queue:work --queue=campaigns,email-validation,default \
    --sleep=3 --tries=3 --timeout=300 --max-jobs=1000 --max-time=3600 &
echo "[entrypoint] Queue worker started"

# ── Start PHP built-in server ──
echo "[entrypoint] Starting web server on port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} server.php
