#!/bin/bash

role=${CONTAINER_ROLE:-app}

echo "Role: ${role}"

if [ "$role" = "app" ]; then
    [[ "${DB_CONNECTION}" == "sqlite" ]] && touch ${DB_DATABASE}

    /usr/sbin/apachectl -D FOREGROUND
elif [ "$role" = "queue" ]; then
    echo "Queue role"
    exit 1
elif [ "$role" = "scheduler" ]; then
    php /var/www/html/app/artisan index:execute --start-queue-worker &

    while [ true ]
    do
      php /var/www/html/app/artisan schedule:run --verbose --no-interaction &
      sleep 60
    done
else
    echo "Could not match the container role \"$role\""
    exit 1
fi