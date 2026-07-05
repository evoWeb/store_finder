MAKEFLAGS += --warn-undefined-variables
SHELL := /bin/bash
.EXPORT_ALL_VARIABLES:
.ONESHELL:
.SHELLFLAGS := -eu -o pipefail -c
.SILENT:

# use the rest as arguments for "run"
_ARGS := $(wordlist 2, $(words $(MAKECMDGOALS)), $(MAKECMDGOALS))
# ...and turn them into do-nothing targets
$(eval $(_ARGS):;@:)

PHP_VERSION := 8.4


##@
##@ Frontend resources related tasks
##@


.PHONY: npm-update
npm-update: ##@ Update node modules
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s npm update


.PHONY: npm-install
npm-install: ##@ Install node modules
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s npm install


.PHONY: build-resources
build-resources: npm-install
build-resources: ##@ Build frontend resource files
	echo "Build frontend resources started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s npm run build
	echo "Build frontend resources finished"


##@
##@ Commands for local task
##@


.PHONY: install
install: ##@ Composer install
	echo "Installed build tools started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s composerInstall
	echo "Installed build tools finished"


.PHONY: cleanup
cleanup: ##@ Cleanup
	echo "Cleanup started"
	Build/Scripts/runTests.sh -s clean
	echo "Cleanup finished";


.PHONY: cleanTests
cleanTests: ##@ Clean test files but leave cache files
	echo "cleanTests started"
	Build/Scripts/runTests.sh -s cleanTests
	echo "cleanTests finished";


.PHONY: phpstan
phpstan: ##@ Run functional tests
	echo "Checking with phpstan started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s phpstan -- $(_ARGS)
	echo "Checking with phpstan finished"


.PHONY: cgl
cgl: ##@ Coding guideline check with
	echo "Coding guideline check with php-cs-fixer started"
	Build/Scripts/runTests.sh -p ${PHP_VERSION} -s cgl -n
	echo "Coding guideline check with php-cs-fixer finished"


.PHONY: functional-test
functional-test: ##@ Run functional tests
	Build/Scripts/runTests.sh -x -p ${PHP_VERSION} -d sqlite -s functional Tests/Functional


help:
	@printf "\nUsage: make \033[32m<command>\033[0m\n"
	grep -F -h "##@" $(MAKEFILE_LIST) | \
	grep -F -v grep -F | \
	grep -F -v awk -F | \
	awk 'BEGIN {FS = ":*[[:space:]]*##@[[:space:]]*"}; \
	{ \
		if ($$2 == "") \
			printf ""; \
		else if ($$0 ~ /^#/) \
			printf "\n%s\n\n", $$2; \
		else if ($$1 == "") \
			printf "     %-30s%s\n", "", $$2; \
		else \
			printf "    \033[32m%-30s\033[0m %s\n", $$1, $$2; \
	}'
.DEFAULT_GOAL := help
