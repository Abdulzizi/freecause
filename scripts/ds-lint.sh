#!/usr/bin/env bash
#
# ds-lint — design-system guard for the public + admin Blade/CSS surface.
# Skips vendor trees (components/blatui is CLI-generated, emails need inline styles).
# Fails on: important-utility bangs, arbitrary type/spacing, disallowed radius,
# disallowed elevation, raw hex colours outside the allowlist.
#
set -uo pipefail
cd "$(dirname "$0")/.."

VIEWS=(resources/views)
CODE=(resources/views resources/js/public resources/js/admin app/Services/ContentRenderer.php)
CSS_FILES=(resources/css/app.css resources/css/admin.css resources/css/client-toast.css)
ALLOW_FILE=scripts/ds-lint-allow.txt
fail=0

report() {
    local label="$1" hits="$2" total="$3"
    fail=1
    printf '✗ %s (%s)\n' "$label" "$total"
    printf '%s\n' "$hits" | head -15
    if [ "$total" -gt 15 ]; then printf '  … %s more\n' "$((total - 15))"; fi
}

scan() {
    local label="$1" pattern="$2"
    shift 2
    local hits total
    hits=$(grep -rnE "$pattern" "$@" --include='*.blade.php' --include='*.js' --include='*.php' \
        --exclude-dir=blatui --exclude-dir=emails 2>/dev/null || true)
    if [ -n "$hits" ]; then
        total=$(printf '%s\n' "$hits" | wc -l | tr -d ' ')
        report "$label" "$hits" "$total"
    fi
}

scan "important utility (! suffix)" '([a-z]+-[a-z0-9-]+!|(block|flex|grid|hidden|inline|contents|table|italic|underline|truncate|uppercase|lowercase|capitalize|antialiased|invisible|static|relative|absolute|fixed|sticky|isolate|collapse)!)(["[:space:]]|$)' "${CODE[@]}"
scan "arbitrary type size (text-[…])" 'text-\[[0-9]' "${VIEWS[@]}"
scan "arbitrary spacing (p/m/gap-[…])" '(^|[[:space:]"'"'"'])(-?)(p|px|py|pt|pb|pl|pr|ps|pe|m|mx|my|mt|mb|ml|mr|ms|me|gap|gap-x|gap-y)-\[[0-9]' "${VIEWS[@]}"
scan "disallowed radius" 'rounded-(xs|xl|2xl|3xl|4xl)([^a-z-]|$)|rounded-\[|rounded([[:space:]"'"'"']|$)' "${VIEWS[@]}"
scan "disallowed elevation" 'shadow-(2xs|xs|sm|md|lg|xl|2xl)([^a-z-]|$)|shadow-\[' "${VIEWS[@]}"
scan "arbitrary colour utility" '(bg|text|border|ring|outline|fill|stroke|from|via|to|decoration|placeholder|caret|accent|divide)-\[#' "${VIEWS[@]}"

scan "dead bootstrap responsive class" 'class="[^"]*(^|[[:space:]])(d|p|m|w|h|text|align-items|justify-content|flex|order|gap|border|rounded|float|overflow)-(sm|md|xl)-[a-z0-9]' "${VIEWS[@]}"

a11y_hits=$(python3 scripts/ds-lint-a11y.py resources/views 2>/dev/null || true)
if [ -n "$a11y_hits" ]; then
    report "accessibility (alt / labels / aria-hidden)" "$a11y_hits" "$(printf '%s\n' "$a11y_hits" | grep -c . || true)"
fi

missing_ui=$(grep '^| `x-ui\.' docs/design-system.md | cut -d'|' -f2 | grep -oE 'x-ui\.[a-z-]+' | sed 's/^x-ui\.//' | while read -r n; do [ -f "resources/views/components/ui/$n.blade.php" ] || echo "x-ui.$n"; done)
if [ -n "$missing_ui" ]; then
    report "documented x-ui component without a file" "$missing_ui" "$(printf '%s\n' "$missing_ui" | wc -l | tr -d ' ')"
fi

hex_hits=$(grep -rnE 'style="[^"]*#[0-9a-fA-F]{3,8}' "${VIEWS[@]}" --include='*.blade.php' --exclude-dir=blatui --exclude-dir=emails 2>/dev/null || true)
if [ -n "$hex_hits" ] && [ -f "$ALLOW_FILE" ]; then
    hex_hits=$(printf '%s\n' "$hex_hits" | grep -vEf <(grep -vE '^[[:space:]]*(#|$)' "$ALLOW_FILE") || true)
fi
if [ -n "$hex_hits" ]; then
    report "raw hex in inline styles" "$hex_hits" "$(printf '%s\n' "$hex_hits" | wc -l | tr -d ' ')"
fi

css_hits=$(grep -nE '#[0-9a-fA-F]{3,8}' "${CSS_FILES[@]}" 2>/dev/null || true)
if [ -n "$css_hits" ]; then
    report "raw hex in stylesheets (tokens.css owns them)" "$css_hits" "$(printf '%s\n' "$css_hits" | wc -l | tr -d ' ')"
fi

if [ "$fail" -eq 0 ]; then
    printf '✓ ds-lint clean\n'
fi
exit "$fail"
