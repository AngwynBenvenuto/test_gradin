#!/usr/bin/env bash
set -euo pipefail
project="${1:-courier-api}"
command -v composer >/dev/null || { echo 'Composer belum diinstall'; exit 1; }
[ ! -e "$project" ] || { echo "Folder $project sudah ada"; exit 1; }
composer create-project laravel/laravel "$project" '10.*'
base="$(cd "$(dirname "$0")" && pwd)"
cp -R "$base/app/." "$project/app/"
cp -R "$base/database/." "$project/database/"
cp "$base/routes/api.php" "$project/routes/api.php"
cp "$base/tests/Feature/CourierApiTest.php" "$project/tests/Feature/CourierApiTest.php"
echo "Selesai. Atur $project/.env, lalu php artisan migrate dan php artisan test --filter=CourierApiTest"
