#!/bin/sh
set -e

# Ensure ownership and permissions for runtime files
if [ -d /var/www/html ]; then
  chown -R www-data:www-data /var/www/html || true
fi

exec "$@"
