#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	if [ "${WAIT_FOR_DB:-true}" = "true" ] && { [ -n "$DATABASE_URL" ] || grep -q ^DATABASE_URL= .env 2>/dev/null; }; then
		echo 'En attente de la base de données...'
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Toujours en attente... $ATTEMPTS_LEFT_TO_REACH_DATABASE tentatives restantes."
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo 'La base de données est injoignable :'
			echo "$DATABASE_ERROR"
			exit 1
		fi

		echo 'Base de données prête.'

		if [ "${RUN_MIGRATIONS:-false}" = "true" ] && [ "$(find ./migrations -iname '*.php' -print -quit 2>/dev/null)" ]; then
			php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
		fi
	fi

	echo 'Application prête !'
fi

exec docker-php-entrypoint "$@"
