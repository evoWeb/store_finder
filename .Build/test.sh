
#!/usr/bin/env bash

export PACKAGE="evoWeb/store_finder";
export T3EXTENSION="store_finder";

runFunctionalTests () {
    local PHP=${1};
    local TYPO3_VERSION=${2};
    local TESTING_FRAMEWORK=${3};
    local DB_DRIVER=${4};
    local COMPOSER="/usr/local/bin/composer";

    rm -rf .Build/Web
    rm -rf .Build/bin

    ${PHP} --version
    ${PHP} ${COMPOSER} --version

    export TYPO3_PATH_WEB=$PWD/.Build/Web;
    ${PHP} ${COMPOSER} require typo3/minimal="$TYPO3_VERSION";
    ${PHP} ${COMPOSER} require --dev typo3/testing-framework="$TESTING_FRAMEWORK";
    git checkout composer.json;

    mkdir -p .Build/Web/typo3conf/ext/
    [ -L ".Build/Web/typo3conf/ext/${T3EXTENSION}" ] || ln -snvf ../../../../. ".Build/Web/typo3conf/ext/${T3EXTENSION}"

    echo "Running php lint";
    errors=$(find . -name \*.php ! -path "./.Build/*" -exec ${PHP} -d display_errors=stderr -l {} 2>&1 >/dev/null \;) && echo "$errors" && test -z "$errors"

    echo "Running xmllint (Xliff) (Remember to install libxml2-utils)";
    find Resources/Private/Language/ -name '*.xlf' -type f | xargs xmllint --noout --schema Tests/Fixtures/xliff-core-1.2-strict.xsd

    echo "Running functional tests";
    export typo3DatabaseName="typo3";
    export typo3DatabaseHost="localhost";
    export typo3DatabaseUsername="root";
    export typo3DatabasePassword="";
    export typo3DatabaseDriver="$DB_DRIVER";
    ${PHP} .Build/bin/phpunit \
        --colors \
        -c .Build/Web/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTests.xml Tests/Functional/;

    rm composer.lock
    rm -rf .Build/Web/
    rm -rf .Build/bin/
    rm -rf var/

    cd ../
}

cd ../;

#runFunctionalTests "/usr/bin/php7.0" "^8.7.0" "~1.3.0" "mysqli";
#runFunctionalTests "/usr/bin/php7.1" "^8.7.0" "~1.3.0" "mysqli";
#runFunctionalTests "/usr/bin/php7.2" "^8.7.0" "~1.3.0" "mysqli";
runFunctionalTests "/usr/bin/php7.2" "^9.5.0" "~4.10.0" "pdo_sqlite";
#runFunctionalTests "/usr/bin/php7.2" "dev-master as 10.0.0" "~4.10.0" "pdo_sqlite";
