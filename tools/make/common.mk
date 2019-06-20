ARTIFACT_INCLUDE_EXISTS := $(shell test -f conf/artifact/include && echo yes || echo no)
ARTIFACT_EXCLUDE_EXISTS := $(shell test -f conf/artifact/exclude && echo yes || echo no)
ARTIFACT_CMD := tar -hczf artifact.tar.gz

ifeq ($(ARTIFACT_INCLUDE_EXISTS),yes)
	ARTIFACT_CMD := ${ARTIFACT_CMD} --files-from=conf/artifact/include
else
	ARTIFACT_CMD := ${ARTIFACT_CMD} *
endif

ifeq ($(ARTIFACT_EXCLUDE_EXISTS),yes)
	ARTIFACT_CMD := ${ARTIFACT_CMD} --exclude-from=conf/artifact/exclude
endif

PHONY += artifact
# This command can always be run on host
artifact: RUN_ON := host
artifact: build ## Make tar.gz package from the current build
	$(call colorecho, "\nCreate artifact (${RUN_ON}):\n")
	@${ARTIFACT_CMD}

PHONY += help
help: ## List all make commands
	$(call colorecho, "\nAvailable make commands:")
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' | sort
