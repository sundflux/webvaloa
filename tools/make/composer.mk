BUILD_TARGETS := composer-install
CLEAN_FOLDERS += vendor
ifeq ($(ENV),production)
	COMPOSER_ARGS := --no-dev --optimize-autoloader --prefer-dist --no-suggest
else
	COMPOSER_ARGS := --no-suggest
endif
COMPOSER_VENDOR_BIN := vendor/bin
PHPCBF_BIN := ${COMPOSER_VENDOR_BIN}/phpcbf
PHPCBF_BIN_EXISTS := $(shell test -f ${PHPCBF_BIN} && echo yes || echo no)

PHONY += composer-info
composer-info: ## Composer info
	$(call colorecho, "\nDo Composer info (${RUN_ON})...\n")
	$(call composer_on_${RUN_ON},info)

PHONY += composer-update
composer-update: ## Update Composer packages
	$(call colorecho, "\nDo Composer update (${RUN_ON})...\n")
	$(call composer_on_${RUN_ON},update --lock)

PHONY += fix
fix: ## Fix code style
	@echo "- ${YELLOW}fix:${NO_COLOR} Start PHP Code Beautifier and Fixer..."
ifeq (${PHPCBF_BIN_EXISTS},yes)
	$(call ${PHPCBF_BIN} fix)
else
	@echo "- ${YELLOW}${PHPCBF_BIN} does not exist! ${RED}[ERROR]${NO_COLOR}"
endif

composer-install: ## Install Composer packages
	$(call colorecho, "\nDo Composer install (${RUN_ON})...\n")
	$(call composer_on_${RUN_ON},install ${COMPOSER_ARGS})

define composer_on_docker
	$(call docker_run_cmd,cd ${DOCKER_PROJECT_ROOT} && composer --ansi $(1))
endef

define composer_on_host
	@composer --ansi $(1)
endef