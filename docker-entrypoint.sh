#!/bin/sh
set -e

# Ensure ownership and permissions for runtime files
if [ -d /var/www/html ]; then
  chmod -R 777 /var/www/html || true
fi

# Start cron service
if [ -f /etc/cron.d/service-auditor ]; then
  crontab /etc/cron.d/service-auditor || true
  service cron start || true
fi

# Optional background worker for URL-triggered scheduling
if [ -f /var/www/html/app/worker.php ]; then
  (php /var/www/html/app/worker.php > /var/log/service-auditor-worker.log 2>&1) &
fi

exec "$@"
