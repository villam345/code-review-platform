#!/bin/sh
set -e

if [ -n "$MYSQLHOST" ] && [ -n "$MYSQLDATABASE" ] && [ -n "$MYSQLUSER" ] && [ -n "$MYSQLPASSWORD" ]; then
  for attempt in $(seq 1 30); do
    if mysql -h"$MYSQLHOST" -P"${MYSQLPORT:-3306}" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" \
      -D"$MYSQLDATABASE" -e "SELECT 1" >/dev/null 2>&1; then
      break
    fi
    echo "Waiting for MySQL..."
    sleep 2
  done

  mysql -h"$MYSQLHOST" -P"${MYSQLPORT:-3306}" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" \
    "$MYSQLDATABASE" < /var/www/html/database/schema.sql
fi

exec "$@"
