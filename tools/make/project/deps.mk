PHONY += install-packages
install-packages: ## Install minimum PHP 7 + dependencies (Ubuntu 18.04)
	$(call colorecho, "\n- Install dependencies (${RUN_ON})...\n")
	$(call installdeps)

define installdeps
	@sudo apt install php7.2-cli php-imagick php7.2-intl php7.2-json php7.2-mbstring php7.2-mysql php7.2-xsl php7.2-xml php-gettext
endef
