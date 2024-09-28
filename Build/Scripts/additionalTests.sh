#!/usr/bin/env bash

#
# TYPO3 core test runner based on docker or podman
#
if [ "${CI}" != "true" ]; then
    trap 'echo "runTests.sh SIGINT signal emitted";cleanUp;exit 2' SIGINT
fi

waitFor() {
    local HOST=${1}
    local PORT=${2}
    local TESTCOMMAND="
        COUNT=0;
        while ! nc -z ${HOST} ${PORT}; do
            if [ \"\${COUNT}\" -gt 10 ]; then
              echo \"Can not connect to ${HOST} port ${PORT}. Aborting.\";
              exit 1;
            fi;
            sleep 1;
            COUNT=\$((COUNT + 1));
        done;
    "
    ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name wait-for-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${IMAGE_ALPINE} /bin/sh -c "${TESTCOMMAND}"
    if [[ $? -gt 0 ]]; then
        kill -SIGINT -$$
    fi
}

cleanUp() {
    echo "Remove container for network \"${NETWORK}\""
    ATTACHED_CONTAINERS=$(${CONTAINER_BIN} ps --filter network=${NETWORK} --format='{{.Names}}')
    for ATTACHED_CONTAINER in ${ATTACHED_CONTAINERS}; do
        ${CONTAINER_BIN} kill ${ATTACHED_CONTAINER} >/dev/null
    done
    if [ ${CONTAINER_BIN} = "docker" ]; then
        ${CONTAINER_BIN} network rm ${NETWORK} >/dev/null
    else
        ${CONTAINER_BIN} network rm -f ${NETWORK} >/dev/null
    fi
}

handleDbmsOptions() {
    # -a, -d, -i depend on each other. Validate input combinations and set defaults.
    case ${DBMS} in
        mariadb)
            [ -z "${DATABASE_DRIVER}" ] && DATABASE_DRIVER="mysqli"
            if [ "${DATABASE_DRIVER}" != "mysqli" ] && [ "${DATABASE_DRIVER}" != "pdo_mysql" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            [ -z "${DBMS_VERSION}" ] && DBMS_VERSION="10.4"
            if ! [[ ${DBMS_VERSION} =~ ^(10.4|10.5|10.6|10.7|10.8|10.9|10.10|10.11|11.0|11.1|11.2|11.3|11.4)$ ]]; then
                echo "Invalid combination -d ${DBMS} -i ${DBMS_VERSION}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        mysql)
            [ -z "${DATABASE_DRIVER}" ] && DATABASE_DRIVER="mysqli"
            if [ "${DATABASE_DRIVER}" != "mysqli" ] && [ "${DATABASE_DRIVER}" != "pdo_mysql" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            [ -z "${DBMS_VERSION}" ] && DBMS_VERSION="8.0"
            if ! [[ ${DBMS_VERSION} =~ ^(8.0|8.1|8.2|8.3|8.4)$ ]]; then
                echo "Invalid combination -d ${DBMS} -i ${DBMS_VERSION}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        postgres)
            if [ -n "${DATABASE_DRIVER}" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            [ -z "${DBMS_VERSION}" ] && DBMS_VERSION="10"
            if ! [[ ${DBMS_VERSION} =~ ^(10|11|12|13|14|15|16)$ ]]; then
                echo "Invalid combination -d ${DBMS} -i ${DBMS_VERSION}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        sqlite)
            if [ -n "${DATABASE_DRIVER}" ]; then
                echo "Invalid combination -d ${DBMS} -a ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            if [ -n "${DBMS_VERSION}" ]; then
                echo "Invalid combination -d ${DBMS} -i ${DATABASE_DRIVER}" >&2
                echo >&2
                echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
                exit 1
            fi
            ;;
        *)
            echo "Invalid option -d ${DBMS}" >&2
            echo >&2
            echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
            exit 1
            ;;
    esac
}

cleanTestFiles() {
    # test related
    echo -n "Clean test related files ... "
    rm -rf \
        .cache \
        bin/ \
        Build/phpunit \
        Documentation-GENERATED-temp/ \
        public/ \
        typo3temp/ \
        var/ \
        vendor/ \
        composer.lock
    git checkout composer.json
    echo "done"
}

getPhpImageVersion() {
    case ${1} in
        8.2)
            echo -n "1.12"
            ;;
        8.3)
            echo -n "1.13"
            ;;
        8.4)
            echo -n "1.2"
            ;;
    esac
}

loadHelp() {
    # Load help text into $HELP
    read -r -d '' HELP <<EOF
TYPO3 core test runner. Execute acceptance, unit, functional and other test suites in
a container based test environment. Handles execution of single test files, sending
xdebug information to a local IDE and more.

Usage: $0 [options] [file]

Options:
    -s <...>
        Specifies the test suite to run
            - buildDocumentation: test build the documentation
            - clean: clean up build, cache and testing related files and folders
            - composerInstallPackage: install a package with composer
            - lintXliff: test XLIFF language files

    -b <docker|podman>
        Container environment:
            - podman (default)
            - docker

    -p <8.2|8.3|8.4>
        Specifies the PHP minor version to be used
            - 8.2 (default): use PHP 8.2
            - 8.3: use PHP 8.3
            - 8.4: use PHP 8.4

    -q
        package to be installed by composer

    -r
        parameters used with composer commands

    -h
        Show this help.

    -v
        Enable verbose script output. Shows variables and docker commands.

Examples:
    # Run install a package with composer
    ./Build/Scripts/additionalTests.sh -p 8.2 -s composerInstallPackage "typo3/cms-core:13.0"

    # Test build the documentation
    ./Build/Scripts/additionalTests.sh -s buildDocumentation

    # Test XLIFF language files
    ./Build/Scripts/additionalTests.sh -s lintXliff
EOF
}

# Test if docker exists, else exit out with error
if ! type "docker" >/dev/null 2>&1 && ! type "podman" >/dev/null 2>&1; then
    echo "This script relies on docker or podman. Please install" >&2
    exit 1
fi

# Go to the directory this script is located, so everything else is relative
# to this dir, no matter from where this script is called, then go up two dirs.
THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1
cd ../../ || exit 1
CORE_ROOT="${PWD}"

# Default variables
TEST_SUITE="unit"
DBMS="sqlite"
DBMS_VERSION=""
PHP_VERSION="8.2"
PHP_XDEBUG_ON=0
PHP_XDEBUG_PORT=9003
ACCEPTANCE_HEADLESS=1
ACCEPTANCE_TOPIC="sets"
CGLCHECK_DRY_RUN=""
DATABASE_DRIVER=""
CHUNKS=0
THISCHUNK=0
CONTAINER_BIN=""
COMPOSER_ROOT_VERSION="13.4.x-dev"
PHPSTAN_CONFIG_FILE="phpstan.local.neon"
CONTAINER_INTERACTIVE="-it --init"
HOST_UID=$(id -u)
HOST_PID=$(id -g)
USERSET=""
CI_PARAMS="${CI_PARAMS:-}"
CI_JOB_ID=${CI_JOB_ID:-}
SUFFIX=$(echo $RANDOM)
if [ ${CI_JOB_ID} ]; then
    SUFFIX="${CI_JOB_ID}-${SUFFIX}"
fi
NETWORK="typo3-core-${SUFFIX}"
CONTAINER_HOST="host.docker.internal"

# additional variables
COMPOSER_PACKAGE=""
COMPOSER_PARAMETER=""
IMAGE_DOCUMENTATION="ghcr.io/typo3-documentation/render-guides:latest"
IMAGE_XMLLINT="registry.gitlab.com/pipeline-components/xmllint:latest"

# Option parsing updates above default vars
# Reset in case getopts has been used previously in the shell
OPTIND=1
# Array for invalid options
INVALID_OPTIONS=()
# Simple option parsing based on getopts (! not getopt)
while getopts ":s:p:q:r:xy:hv" OPT; do
    case ${OPT} in
        s)
            TEST_SUITE=${OPTARG}
            ;;
        p)
            PHP_VERSION=${OPTARG}
            if ! [[ ${PHP_VERSION} =~ ^(8.1|8.2|8.3)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            ;;
        q)
            COMPOSER_PACKAGE=${OPTARG}
            ;;
        r)
            COMPOSER_PARAMETER=${OPTARG}
            ;;
        x)
            PHP_XDEBUG_ON=1
            ;;
        h)
            loadHelp
            echo "${HELP}"
            exit 0
            ;;
        \?)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
        :)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
    esac
done

# Exit on invalid options
if [ ${#INVALID_OPTIONS[@]} -ne 0 ]; then
    echo "Invalid option(s):" >&2
    for I in "${INVALID_OPTIONS[@]}"; do
        echo "-"${I} >&2
    done
    echo >&2
    echo "Use \".Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
    exit 1
fi

handleDbmsOptions

# ENV var "CI" is set by gitlab-ci. Use it to force some CI details.
if [ "${CI}" == "true" ]; then
    PHPSTAN_CONFIG_FILE="phpstan.ci.neon"
    CONTAINER_INTERACTIVE=""
fi

# determine default container binary to use: 1. podman 2. docker
if [[ -z "${CONTAINER_BIN}" ]]; then
    if type "podman" >/dev/null 2>&1; then
        CONTAINER_BIN="podman"
    elif type "docker" >/dev/null 2>&1; then
        CONTAINER_BIN="docker"
    fi
fi

if [ $(uname) != "Darwin" ] && [ ${CONTAINER_BIN} = "docker" ]; then
    # Run docker jobs as current user to prevent permission issues. Not needed with podman.
    USERSET="--user $HOST_UID"
fi

if ! type ${CONTAINER_BIN} >/dev/null 2>&1; then
    echo "Selected container environment \"${CONTAINER_BIN}\" not found. Please install or use -b option to select one." >&2
    exit 1
fi

IMAGE_APACHE="ghcr.io/typo3/core-testing-apache24:1.5"
IMAGE_PHP="ghcr.io/typo3/core-testing-$(echo "php${PHP_VERSION}" | sed -e 's/\.//'):$(getPhpImageVersion $PHP_VERSION)"

IMAGE_NODEJS="ghcr.io/typo3/core-testing-nodejs22:1.1"
IMAGE_NODEJS_CHROME="ghcr.io/typo3/core-testing-nodejs22-chrome:1.1"
IMAGE_PLAYWRIGHT="mcr.microsoft.com/playwright:v1.45.1-jammy"
IMAGE_ALPINE="docker.io/alpine:3.8"
IMAGE_SELENIUM="docker.io/selenium/standalone-chrome:4.20.0-20240505"
IMAGE_REDIS="docker.io/redis:4-alpine"
IMAGE_MEMCACHED="docker.io/memcached:1.5-alpine"
IMAGE_MARIADB="docker.io/mariadb:${DBMS_VERSION}"
IMAGE_MYSQL="docker.io/mysql:${DBMS_VERSION}"
IMAGE_POSTGRES="docker.io/postgres:${DBMS_VERSION}-alpine"

# Detect arm64 to use seleniarm image.
ARCH=$(uname -m)
if [ ${ARCH} = "arm64" ]; then
    IMAGE_SELENIUM="docker.io/seleniarm/standalone-chromium:4.20.0-20240427"
fi

# Remove handled options and leaving the rest in the line, so it can be passed raw to commands
shift $((OPTIND - 1))

# Create .cache dir: composer and various npm jobs need this.
mkdir -p .cache
mkdir -p typo3temp/var/tests

${CONTAINER_BIN} network create ${NETWORK} >/dev/null

if [ ${CONTAINER_BIN} = "docker" ]; then
    # docker needs the add-host for xdebug remote debugging. podman has host.container.internal built in
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} --rm --network ${NETWORK} --add-host "${CONTAINER_HOST}:host-gateway" ${USERSET} -v ${CORE_ROOT}:${CORE_ROOT} -w ${CORE_ROOT}"
else
    # podman
    CONTAINER_HOST="host.containers.internal"
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm --network ${NETWORK} -v ${CORE_ROOT}:${CORE_ROOT} -w ${CORE_ROOT}"
fi

if [ ${PHP_XDEBUG_ON} -eq 0 ]; then
    XDEBUG_MODE="-e XDEBUG_MODE=off"
    XDEBUG_CONFIG=" "
    PHP_FPM_OPTIONS="-d xdebug.mode=off"
else
    XDEBUG_MODE="-e XDEBUG_MODE=debug -e XDEBUG_TRIGGER=foo"
    XDEBUG_CONFIG="client_port=${PHP_XDEBUG_PORT} client_host=${CONTAINER_HOST}"
    PHP_FPM_OPTIONS="-d xdebug.mode=debug -d xdebug.start_with_request=yes -d xdebug.client_host=${CONTAINER_HOST} -d xdebug.client_port=${PHP_XDEBUG_PORT} -d memory_limit=256M"
fi

# Suite execution
case ${TEST_SUITE} in
    clean)
        cleanTestFiles
        ;;
    buildDocumentation)
        COMMAND="render ${CORE_ROOT}/Documentation"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name build-documentation-${SUFFIX} ${IMAGE_DOCUMENTATION} ${COMMAND}
        SUITE_EXIT_CODE=$?
        ;;
    lintXliff)
        COMMAND="xmllint --schema ${CORE_ROOT}/Build/xliff-core-1.2-strict.xsd --noout --path ${CORE_ROOT}/Resources/Private/Language/*.xlf"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name lint-xliff-${SUFFIX} ${IMAGE_XMLLINT} ${COMMAND}
        SUITE_EXIT_CODE=$?
        ;;
    composerInstallPackage)
        COMMAND=(composer require -W -n ${COMPOSER_PARAMETER} ${COMPOSER_PACKAGE})
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-install-package-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" -e COMPOSER_CACHE_DIR=.cache/composer -e COMPOSER_ROOT_VERSION=${COMPOSER_ROOT_VERSION} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
esac

cleanUp

# Print summary
echo "" >&2
echo "###########################################################################" >&2
echo "Result of ${TEST_SUITE}" >&2
echo "Container runtime: ${CONTAINER_BIN}" >&2
echo "Container suffix: ${SUFFIX}"
echo "PHP: ${PHP_VERSION}" >&2
if [[ ${TEST_SUITE} =~ ^(functional|acceptance|acceptanceComposer|acceptanceInstall)$ ]]; then
    case "${DBMS}" in
        mariadb|mysql|postgres)
            echo "DBMS: ${DBMS}  version ${DBMS_VERSION}  driver ${DATABASE_DRIVER}" >&2
            ;;
        sqlite)
            echo "DBMS: ${DBMS}" >&2
            ;;
    esac
fi
if [[ ${SUITE_EXIT_CODE} -eq 0 ]]; then
    echo "SUCCESS" >&2
else
    echo "FAILURE" >&2
fi
echo "###########################################################################" >&2
echo "" >&2

# Exit with code of test suite - This script return non-zero if the executed test failed.
exit $SUITE_EXIT_CODE
