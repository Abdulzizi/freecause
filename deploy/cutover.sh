#!/usr/bin/env bash
#
# Phase 1 cutover helper. Prepares a Deployer layout at APP_ROOT by copying the
# runtime state (gitignored paths) out of the current in-place install. Dry-run
# by default; pass --apply to execute. It does NOT touch nginx, supervisor, or
# run a deploy. See deploy/README.md.
#
#   APP_ROOT=/var/www/freecause OLD_ROOT=/var/www/freecause-release-v2026.09.19 \
#     bash deploy/cutover.sh            # dry-run
#   ... bash deploy/cutover.sh --apply  # execute
#
set -euo pipefail

APP_ROOT="${APP_ROOT:-/var/www/freecause}"
OLD_ROOT="${OLD_ROOT:-/var/www/freecause-release-v2026.09.19}"
RUN_USER="${RUN_USER:-www-data}"

APPLY=0
[ "${1:-}" = "--apply" ] && APPLY=1

run() {
  if [ "$APPLY" = "1" ]; then
    "$@"
  else
    printf '[dry-run] %s\n' "$*"
  fi
}

copy() {
  local src="$1" dest="$2"
  if [ ! -e "$src" ]; then
    printf 'skip (absent): %s\n' "$src"
    return
  fi
  if [ -e "$dest" ]; then
    printf 'skip (exists): %s\n' "$dest"
    return
  fi
  run mkdir -p "$(dirname "$dest")"
  run cp -a "$src" "$dest"
}

run mkdir -p "$APP_ROOT/releases" "$APP_ROOT/shared"

copy "$OLD_ROOT/.env" "$APP_ROOT/shared/.env"
copy "$OLD_ROOT/storage" "$APP_ROOT/shared/storage"

copy "$OLD_ROOT/public/magazine/wp-config.php" "$APP_ROOT/shared/public/magazine/wp-config.php"
copy "$OLD_ROOT/public/magazine/.htaccess" "$APP_ROOT/shared/public/magazine/.htaccess"

for d in uploads cache upgrade upgrade-temp-backup updraft updraft_backup; do
  copy "$OLD_ROOT/public/magazine/wp-content/$d" "$APP_ROOT/shared/public/magazine/wp-content/$d"
done

run chown -R "$RUN_USER:$RUN_USER" "$APP_ROOT/shared"
run chmod 600 "$APP_ROOT/shared/.env"

cat <<EOF

Prepared (or planned) $APP_ROOT.

Next, manually and only after review:
  1. Repoint nginx root to $APP_ROOT/current/public
     (back up the vhost, nginx -t, reload).
  2. Install deploy/supervisor/freecause-release-queue.conf, then
     supervisorctl reread && supervisorctl update.
  3. Run the first Deployer release (workflow "Deploy (Deployer)").
  4. Verify /version and /up, then stop the old release's queue workers.

Rollback: repoint nginx root back to $OLD_ROOT/public and restart the old workers.
EOF
