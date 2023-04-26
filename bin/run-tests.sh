#!/usr/bin/env bash

set -e

echo "Installing the test environment..."

docker-compose exec -u www-data wordpress \
	/var/www/html/wp-content/plugins/woocommerce-rave/bin/install-wp-tests.sh

echo "Running the tests..."

docker-compose exec -u www-data wordpress \
	/var/www/html/wp-content/plugins/woocommerce-rave/vendor/bin/phpunit \
	--configuration /var/www/html/wp-content/plugins/woocommerce-rave/phpunit.xml.dist \
	$*