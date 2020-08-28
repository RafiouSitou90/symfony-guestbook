SHELL := /bin/bash
s := symfony
sc := symfony console


## Create database
database:
	$(sc) doctrine:database:drop --force -n
	$(sc) doctrine:database:create
.PHONY: database


## Create migrations
migrations:
	$(sc) doctrine:migrations:migrate -n
.PHONY: migrations


## Create test database
database-test:
	$(sc) doctrine:database:drop --force --env=test
	$(sc) doctrine:database:create --env=test
	$(sc) doctrine:schema:update --force -n --env=test
.PHONY: database-test

## Create dev fixtures
fixtures:
	$(sc) doctrine:fixtures:load -n
.PHONY: fixtures

## Create test fixtures
fixtures-test:
	$(sc) doctrine:fixtures:load -n --env=test
.PHONY: fixtures-test

## Run messenger workers in background
workers:
	$(s) run -d --watch=config,src,templates,vendor $(sc) messenger:consume async
.PHONY: workers

## Run all tests
tests:
	$(s) php bin/phpunit
.PHONY: tests
