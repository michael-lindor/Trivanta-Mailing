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
php artisan migrate --force --no-interaction 2>&1 || echo "[entrypoint] WARNING: migrations failed — continuing startup"

# ── First-time install: create admin + mark installed ──
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

    # Activate Cold Email Outreach addon
    php -r "
        require __DIR__.'/vendor/autoload.php';
        \$app = require_once __DIR__.'/bootstrap/app.php';
        \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
        \$kernel->bootstrap();
        if (!\App\Models\Addon::where('slug','cold-email-outreach')->exists()) {
            \App\Models\Addon::create([
                'slug'=>'cold-email-outreach',
                'name'=>'Cold Email Outreach',
                'author'=>'MailPurse',
                'version'=>'1.0.0',
                'category'=>'outreach',
                'description'=>'Run cold email outreach campaigns with sequences, A/B testing, reply detection, and scheduling.',
                'status'=>'active',
                'license_key'=>'self-hosted',
                'installed_at'=>now(),
                'activated_at'=>now(),
            ]);
            echo \"[entrypoint] Cold Email Outreach addon activated\n\";
        }
    " 2>&1 || echo "[entrypoint] WARNING: addon activation had errors"

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

# ── Create router script for PHP built-in server ──
cat > /app/docker-router.php <<'ROUTEREOF'
<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');

// Serve static files directly from public/
if ($uri !== '/' && is_file(__DIR__ . '/public' . $uri)) {
    $path = __DIR__ . '/public' . $uri;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
        'webp' => 'image/webp',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    readfile($path);
    return true;
}

// Route everything else through index.php
chdir(__DIR__);
require __DIR__ . '/index.php';
ROUTEREOF

# ── Start PHP built-in server ──
echo "[entrypoint] Starting web server on port ${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} /app/docker-router.php
