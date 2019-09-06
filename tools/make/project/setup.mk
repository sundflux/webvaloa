#PHONY += install
#install: ## Install Webvaloa
#	$(call colorecho, "\n- Make installation (${RUN_ON})...\n")
#	$(call webvaloa,-c installer -p setup/cms)
#
#define webvaloa
#	@composer --ansi install
#	@php index.php $(1)
# endef
