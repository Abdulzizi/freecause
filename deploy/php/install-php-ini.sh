#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SRC_INI="$SCRIPT_DIR/freecause-uploads.ini"

if [[ ! -f "$SRC_INI" ]]; then
    echo "Missing source ini: $SRC_INI" >&2
    exit 1
fi

PHP_BIN="${PHP_BIN:-php}"

PHP_INFO="$("$PHP_BIN" -i)"
SCAN_DIR="$(grep -m1 'Scan this dir for additional' <<<"$PHP_INFO" | sed -E 's/.*=> //')"
if [[ -z "${SCAN_DIR:-}" || "$SCAN_DIR" == "(none)" ]]; then
    echo "Could not detect a PHP conf.d scan dir from: $PHP_BIN" >&2
    exit 1
fi

if [[ ! -d "$SCAN_DIR" ]]; then
    if mkdir -p "$SCAN_DIR" 2>/dev/null; then :; else sudo mkdir -p "$SCAN_DIR"; fi
fi

DEST_INI="$SCAN_DIR/zz-freecause-uploads.ini"

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

if [[ -n "${PHP_FPM_SERVICE:-}" ]]; then
    restart_service "$PHP_FPM_SERVICE"
elif command -v systemctl >/dev/null 2>&1; then
    FPM_UNIT="$(systemctl list-unit-files 2>/dev/null | awk '/^php[0-9.]*-fpm/ {print $1; exit}')"
    [[ -n "${FPM_UNIT:-}" ]] && restart_service "$FPM_UNIT"
elif command -v brew >/dev/null 2>&1; then
    brew services restart php@8.5 2>/dev/null || brew services restart php 2>/dev/null || true
    echo "Restarted brew php service"
fi

if [[ -n "${WEB_SERVICE:-}" ]]; then
    restart_service "$WEB_SERVICE"
elif command -v systemctl >/dev/null 2>&1; then
    for svc in nginx apache2 httpd; do
        restart_service "$svc"
    done
fi

echo "--- Effective PHP upload limits ---"
"$PHP_BIN" -i | grep -E "^(upload_max_filesize|post_max_size)" || true
