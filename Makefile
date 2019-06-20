PHONY :=
PROJECT_DIR := $(dir $(lastword $(MAKEFILE_LIST)))

# Include project env vars (if exists)
-include .env

# Include druidfi/tools config
include $(PROJECT_DIR)/tools/make/Makefile

# Include project specific make files (if they exist)
-include $(PROJECT_DIR)/tools/make/project/*.mk

.PHONY: $(PHONY)
