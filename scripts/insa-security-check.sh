#!/usr/bin/env bash
# INSA security readiness re-check — NON-DESTRUCTIVE commands only.
# Usage: bash scripts/insa-security-check.sh  (run from the project root)
# Output is written to stdout and to a timestamped report file.

set -uo pipefail

REPORT="insa-security-check-$(date +%Y%m%d-%H%M%S).log"
run() {
    echo ""            | tee -a "$REPORT"
    echo "==== $* ====" | tee -a "$REPORT"
    "$@" 2>&1           | tee -a "$REPORT"
}

echo "INSA security check — $(date)" | tee "$REPORT"

run php artisan about
run php artisan route:list --except-vendor
run composer audit
run npm audit
run php artisan test

echo "" | tee -a "$REPORT"
echo "==== secret scan (tracked files) ====" | tee -a "$REPORT"
# Flags non-empty PASSWORD/SECRET/KEY assignments in tracked env templates.
git ls-files '*.env*' '.env*' | while read -r f; do
    grep -HnE '(PASSWORD|SECRET|_KEY)=[^[:space:]]+' "$f" \
        | grep -vE '=(null|local|base64:)?$' \
        | grep -vE 'PLACEHOLDER|example\.com' || true
done | tee -a "$REPORT"

echo "" | tee -a "$REPORT"
echo "Report saved to $REPORT" | tee -a "$REPORT"
