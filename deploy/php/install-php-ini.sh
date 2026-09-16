#!/usr/bin/env bash
#
# Install FreeCause PHP upload limits into the active PHP conf.d directory and
# restart PHP-FPM + the web server. Idempotent and safe to run repeatedly.
#
# Usage:
#   deploy/php/install-php-ini.sh                # auto-detect PHP-FPM + web server
#   PHP_FPM_SERVICE=php8.2-fpm WEB_SERVICE=nginx deploy/php/install-php-ini.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC_INI="$SCRIPT_DIR/freecause-uploads.ini"

if [[ ! -f "$SRC_INI" ]]; then
    echo "Missing source ini: $SRC_INI" >&2
    exit 1
fi

# Resolve the PHP CLI (allow override, e.g. brew's php@8.5 on macOS).
PHP_BIN="${PHP_BIN:-php}"

# Find the conf.d scan dir the running PHP actually uses.
# Read the full output (no early awk exit) to avoid SIGPIPE under pipefail.
PHP_INFO="$("$PHP_BIN" -i)"
SCAN_DIR="$(grep -m1 'Scan this dir for additional' <<<"$PHP_INFO" | sed -E 's/.*=> //')"
if [[ -z "${SCAN_DIR:-}" || "$SCAN_DIR" == "(none)" ]]; then
    echo "Could not detect a PHP conf.d scan dir from: $PHP_BIN" >&2
    exit 1
fi

# The scan dir is what PHP is configured to read; create it if the install
# hasn't yet (common with fresh Homebrew PHP).
if [[ ! -d "$SCAN_DIR" ]]; then
    if mkdir -p "$SCAN_DIR" 2>/dev/null; then :; else sudo mkdir -p "$SCAN_DIR"; fi
fi

DEST_INI="$SCAN_DIR/zz-freecause-uploads.ini"

# Copy (needs sudo on system paths like /etc/php/... but not on brew paths).
if [[ -w "$SCAN_DIR" ]]; then
    cp "$SRC_INI" "$DEST_INI"
else
    sudo cp "$SRC_INI" "$DEST_INI"
fi
echo "Installed: $DEST_INI"

restart_service() {
    local svc="$1"
    if command -v systemctl >/dev/null 2>&1 && systemctl list-unit-files 2>/dev/null | grep -q "^${svc}"; then
        sudo systemctl restart "$svc" && echo "Restarted: $svc"
    fi
}

# Restart PHP-FPM (Linux). On macOS/brew, restart the php service instead.
if [[ -n "${PHP_FPM_SERVICE:-}" ]]; then
    restart_service "$PHP_FPM_SERVICE"
elif command -v systemctl >/dev/null 2>&1; then
    # Auto-detect a php*-fpm unit.
    FPM_UNIT="$(systemctl list-unit-files 2>/dev/null | awk '/^php[0-9.]*-fpm/ {print $1; exit}')"
    [[ -n "${FPM_UNIT:-}" ]] && restart_service "$FPM_UNIT"
elif command -v brew >/dev/null 2>&1; then
    brew services restart php@8.5 2>/dev/null || brew services restart php 2>/dev/null || true
    echo "Restarted brew php service"
fi

# Restart the web server if one is present.
if [[ -n "${WEB_SERVICE:-}" ]]; then
    restart_service "$WEB_SERVICE"
elif command -v systemctl >/dev/null 2>&1; then
    for svc in nginx apache2 httpd; do
        restart_service "$svc"
    done
fi

# Verify the effective values.
echo "--- Effective PHP upload limits ---"
"$PHP_BIN" -i | grep -E "^(upload_max_filesize|post_max_size)" || true
