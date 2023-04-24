.DEFAULT_GOAL := init

%:
	@:

init:
	echo "Specify an Action"

dev-js:
	npm run preuglify && npm run uglify

build-production-js:
	npm run prod

build-production-docs:
	npm run docs:build

dev-docs:
	npm run docs:dev

wp-format:
	npm run format

i18n-pot:
	composer run makepot

zip:
	rm woocommerce-rave.zip && npm run plugin-zip

inspection:
	./vendor/bin/phpcs -p . --standard=PHPCompatibilityWP

build: i18n-pot && build-production-js && zip

release: build
