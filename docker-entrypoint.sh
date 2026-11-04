#!/bin/sh
set -e

if [ -n "$MYSQLHOST" ] && [ -n "$MYSQLDATABASE" ] && [ -n "$MYSQLUSER" ] && [ -n "$MYSQLPASSWORD" ]; then
  if mysql --ssl-mode=DISABLED -h"$MYSQLHOST" -P"${MYSQLPORT:-3306}" -u"$MYSQLUSER" -p"$MYSQLPASSWORD" \
    "$MYSQLDATABASE" < /var/www/html/database/schema.sql >/dev/null 2>&1; then
    echo "Database schema is ready."
  else
    echo "Database schema import skipped; MySQL variables may not be ready yet."
  fi
fi

exec "$@"
