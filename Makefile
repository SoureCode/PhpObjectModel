.DEFAULT_GOAL := help

EXEC = php
COMPOSER = $(EXEC) composer

ifndef CI_JOB_ID
	GREEN  := $(shell tput -Txterm setaf 2)
	YELLOW := $(shell tput -Txterm setaf 3)
	RESET  := $(shell tput -Txterm sgr0)
	TARGET_MAX_CHAR_NUM=30
endif

help:
	@echo "PhpObjectModel"
	@awk '/^[a-zA-Z\-\_0-9]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")-1); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "  ${GREEN}%-$(TARGET_MAX_CHAR_NUM)s${RESET} %s\n", helpCommand, helpMessage; \
		} \
		isTopic = match(lastLine, /^###/); \
	    if (isTopic) { \
			topic = substr($$1, 0, index($$1, ":")-1); \
			printf "\n${YELLOW}%s${RESET}\n", topic; \
		} \
	} { lastLine = $$0 }' $(MAKEFILE_LIST)



#################################
Project:

## Install the whole dev environment
install:
	@$(COMPOSER) update

#################################
Tests:

## Run codestyle static analysis
php-cs-fixer:
	@$(EXEC) vendor/bin/php-cs-fixer fix --dry-run --diff

## Run psalm static analysis
psalm:
	@$(EXEC) vendor/bin/psalm --show-info=true --no-cache

## Run phpunit tests
phpunit:
	@$(EXEC) vendor/bin/phpunit

codesniffer:
	@$(EXEC) vendor/bin/phpcs --standard=PSR12 src

## Run either static analysis and tests
ci: php-cs-fixer psalm codesniffer phpunit

.PHONY: php-cs-fixer psalm codesniffer phpunit ci

#################################
Tools:

## Fix PHP files to be compliant with coding standards
fix-cs:
	@$(EXEC) vendor/bin/php-cs-fixer fix
	@$(EXEC) vendor/bin/phpcbf --standard=PSR12 src

.PHONY: fix-cs
