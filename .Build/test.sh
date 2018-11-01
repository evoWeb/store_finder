#!/usr/bin/env bash

runFunctionalTests () {
    PHP=${1};
    PACKAGE=${2};
    T3EXTENSION=${3};
    TYPO3_VERSION=${4};
    TESTING_FRAMEWORK=${5};
    DB_DRIVER=${6};
    COMPOSER="/usr/local/bin/composer";

    rm -rf ./*
    rm -rf ./.*

    git clone --depth=50 --branch=develop "https://github.com/$PACKAGE.git" "$PACKAGE"
    cd "$PACKAGE"

    ${PHP} --version
    ${PHP} ${COMPOSER} --version

    export TYPO3_PATH_WEB=$PWD/.Build/Web; \
        ${PHP} ${COMPOSER} require typo3/minimal="$TYPO3_VERSION"; \
        ${PHP} ${COMPOSER} require --dev typo3/testing-framework="$TESTING_FRAMEWORK"; \
        git checkout composer.json;

    mkdir -p .Build/Web/typo3conf/ext/
    [ -L ".Build/Web/typo3conf/ext/$T3EXTENSION" ] || ln -snvf ../../../../. ".Build/Web/typo3conf/ext/$T3EXTENSION"

    echo "Running php lint";
    errors=$(find . -name \*.php ! -path "./.Build/*" -exec ${PHP} -d display_errors=stderr -l {} 2>&1 >/dev/null \;) && echo "$errors" && test -z "$errors"

    echo "Running xmllint (Xliff)";
    find Resources/Private/Language/ -name '*.xlf' -type f | xargs xmllint --noout --schema Tests/Fixtures/xliff-core-1.2-strict.xsd

    echo "Running functional tests"; \
        export typo3DatabaseName="typo3"; \
        export typo3DatabaseHost="localhost"; \
        export typo3DatabaseUsername="root"; \
        export typo3DatabasePassword=""; \
        export typo3DatabaseDriver="pdo_sqlite"; \
        ${PHP} .Build/bin/phpunit \
            --colors \
            -c .Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml Tests/Functional/;

    rm composer.lock
    rm -rf .Build/Web/
    rm -rf var/
}

runFunctionalTests "/usr/bin/php7.2" "evoWeb/store_finder" "store_finder" "^9.5.0" "~4.10.0" "pdo_sqlite";
