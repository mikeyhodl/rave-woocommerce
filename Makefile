.DEFAULT_GOAL := init

%:
	@:

init:
	echo "Specify an Action"

dev:
	npm run dev

build:
	npm run prod

docs-build:
	npm run docs:build

devdocs:
	npm run docs:dev

wp-format:
	npm run format

zip:
	npm run plugin-zip

inspection:
	./vendor/bin/phpcs -p . --standard=PHPCompatibilityWP

clean:
	npm run clean
