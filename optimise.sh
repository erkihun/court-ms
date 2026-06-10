#!/usr/bin/env bash
# =============================================================================
#  Court MS — Production Optimisation Script
#  Run this on your live server after every deployment (git pull / FTP upload)
#  Usage: bash optimise.sh
# =============================================================================

set -e

echo ""
echo "═══════════════════════════════════════════════"
echo "  Court MS — Production Optimise"
echo "═══════════════════════════════════════════════"
echo ""

# ── 1. Install PHP dependencies (no dev packages) ────────────────────────────
echo "▶  Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --quiet
echo "   ✓ composer done"

# ── 2. Run any pending database migrations ───────────────────────────────────
echo "▶  Running migrations..."
php artisan migrate --force
echo "   ✓ migrations done"

# ── 3. Clear ALL stale caches before rebuilding ──────────────────────────────
echo "▶  Clearing stale caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
echo "   ✓ caches cleared"

# ── 4. Rebuild optimised caches ──────────────────────────────────────────────
echo "▶  Building optimised caches..."
php artisan config:cache      # merges all config files into one file
php artisan route:cache       # compiles all routes into one file
php artisan view:cache        # pre-compiles all Blade templates
php artisan event:cache       # caches event/listener discovery
echo "   ✓ caches built"

# ── 5. Optimise the class autoloader ─────────────────────────────────────────
echo "▶  Optimising autoloader..."
php artisan optimize
echo "   ✓ autoloader optimised"

# ── 6. Build frontend assets ─────────────────────────────────────────────────
echo "▶  Building frontend assets..."
npm ci --silent
npm run build
echo "   ✓ assets built"

# ── 7. Set correct file permissions ──────────────────────────────────────────
echo "▶  Setting storage permissions..."
chmod -R 775 storage bootstrap/cache
echo "   ✓ permissions set"

# ── 8. Restart queue workers (if any) ────────────────────────────────────────
echo "▶  Restarting queue workers..."
php artisan queue:restart
echo "   ✓ queue restarted"

echo ""
echo "═══════════════════════════════════════════════"
echo "  ✓ Optimisation complete — site is live"
echo "═══════════════════════════════════════════════"
echo ""
