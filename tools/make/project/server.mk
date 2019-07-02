PHONY += server
server: ## Start development environment on localhost:8000
	$(call colorecho, "\n- Start development environment (${RUN_ON})...\n")
	$(call webvaloa_server)

define webvaloa_server
	@php -S localhost:8000
endef
