#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

PHP=php
if [ -x /opt/homebrew/opt/php/bin/php ]; then PHP=/opt/homebrew/opt/php/bin/php; fi

cmd="${1:-}"
[ -n "$cmd" ] || { echo "usage: scripts/blatui.sh add|update [components...]" >&2; exit 1; }
shift

case "$cmd" in
    add) "$PHP" artisan blatui:add --path=resources/views/components/blatui "$@" ;;
    update) "$PHP" artisan blatui:update --path=resources/views/components/blatui "$@" ;;
    *) echo "usage: scripts/blatui.sh add|update [components...]" >&2; exit 1 ;;
esac

sed -i '' 's|</x-ui\.|</x-blatui.|g; s|<x-ui\.|<x-blatui.|g' resources/views/components/blatui/*.blade.php
"$PHP" artisan blatui:doctor || true
