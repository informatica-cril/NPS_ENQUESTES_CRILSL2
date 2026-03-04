#!/bin/bash
# Create tmp dir for PHP and set permissive permissions if needed
set -e
PROJECT_DIR="/www/wwwroot/enquestes.crilsl.org"
TMP_DIR="$PROJECT_DIR/storage/framework/tmp"

if [ ! -d "$TMP_DIR" ]; then
  mkdir -p "$TMP_DIR"
  echo "Created $TMP_DIR"
else
  echo "$TMP_DIR already exists"
fi

# Try to set safe ownership, fallback to chmod 0777
if id www-data >/dev/null 2>&1; then
  chown -R www-data:www-data "$PROJECT_DIR/storage"
  echo "Set owner to www-data"
elif id apache >/dev/null 2>&1; then
  chown -R apache:apache "$PROJECT_DIR/storage"
  echo "Set owner to apache"
else
  chmod -R 0777 "$PROJECT_DIR/storage"
  echo "Set storage permissions to 0777 (fallback)"
fi

echo "Done. Verify with: php -r 'echo sys_get_temp_dir();'"
