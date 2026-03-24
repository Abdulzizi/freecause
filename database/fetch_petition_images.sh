#!/usr/bin/env bash
# database/fetch_petition_images.sh
#
# Fetches petition images from old server to local storage.
# Resumable: skips files that already exist locally.
#
# Usage:
#   bash database/fetch_petition_images.sh             # full sync
#   bash database/fetch_petition_images.sh --dry-run   # preview only
#
# Run from the Laravel project root.

set -euo pipefail

OLD_HOST="azizi@138.197.120.103"
OLD_PATH="~/public_html/pics/"
LOCAL_PATH="storage/app/public/petitions/"

DRY_RUN=""
if [[ "${1:-}" == "--dry-run" ]]; then
    DRY_RUN="--dry-run"
    echo "[DRY RUN] No files will be transferred."
fi

echo "Syncing petition images from ${OLD_HOST}:${OLD_PATH}"
echo "  -> ${LOCAL_PATH}"
echo ""

rsync -az \
    --ignore-existing \
    --progress \
    --stats \
    --human-readable \
    -e "ssh -o StrictHostKeyChecking=accept-new" \
    ${DRY_RUN} \
    "${OLD_HOST}:${OLD_PATH}" \
    "${LOCAL_PATH}"

echo ""
echo "Done. Run 'php database/verify_petition_images.php' to check coverage."
