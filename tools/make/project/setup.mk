PHONY += install
install: ## Install Webvaloa
	$(call colorecho, "\n- Make installation (${RUN_ON})...\n")
	$(call webvaloa,-c installer)

define webvaloa
	@php index.php $(1)
endef
