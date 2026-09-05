#!/usr/bin/env bash
# Build distributable ZIP with vendor/ for shared hosting + /install wizard.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
echo "==> composer install --no-dev"
composer install --no-dev --optimize-autoloader --no-interaction
STAGE=$(mktemp -d)
NAME="codebazaar-laravel-release"
mkdir -p "$STAGE/$NAME"
if command -v rsync >/dev/null 2>&1; then
  rsync -a --exclude='.git' --exclude='node_modules' --exclude='.env' \
    --exclude='dist' --exclude='storage/installed' \
    "$ROOT/" "$STAGE/$NAME/"
else
  cp -R "$ROOT"/. "$STAGE/$NAME/"
  rm -rf "$STAGE/$NAME/.git" "$STAGE/$NAME/dist" 2>/dev/null || true
fi
if [ -f "$ROOT/.env.install" ]; then
  cp "$ROOT/.env.install" "$STAGE/$NAME/.env"
fi
mkdir -p "$STAGE/$NAME/storage/framework/"{cache,sessions,views} \
  "$STAGE/$NAME/storage/logs" "$STAGE/$NAME/bootstrap/cache"
OUT="$ROOT/dist/${NAME}.zip"
mkdir -p "$ROOT/dist"
(cd "$STAGE" && zip -r -q "$OUT" "$NAME")
echo "Built: $OUT"
echo "Upload, extract, open https://yoursite.com/install"
