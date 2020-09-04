SHELL := /bin/bash
s := symfony
sc := symfony console

## Clear cache
cache:
	$(sc) cache:clear
	$(sc) cache:warmup
.PHONY: cache


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


## Generate workflow image in public/uploads/ folder
show-workflow:
	$(sc) workflow:dump comment | dot -Tpng -o public/uploads/workflow.png
.PHONY: show-workflow

## Generate traduction files
translation:
	$(sc) translation:update fr --force --domain=messages
	$(sc) translation:update en --force --domain=messages
.PHONY: translation


## Run all tests
tests:
	$(s) php bin/phpunit
.PHONY: tests
