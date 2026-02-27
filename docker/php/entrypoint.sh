#!/bin/sh
set -e

# Fix permission once on startup (only if running as root)
if [ "$(id -u)" = "0" ]; then
  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
  chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
fi

exec "$@"
