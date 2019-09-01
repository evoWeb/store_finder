TYPO3 Extension ``store_finder`` [![Build Status](https://travis-ci.org/evoWeb/store_finder.svg?branch=master)](https://travis-ci.org/evoWeb/store_finder)
=================

## Installation

**Installation via Composer**

Its recommended to install the extension via composer. Either add it to your composer.json
in the TYPO3 project root or in the project root just enter

composer require evoweb/store-finder

**Installation from TYPO3 Extension Repository**

Download and install the extension with the extension manger module or directly from the
[TER](https://typo3.org/extensions/repository/view/store_finder).

## TYPO3 10.x compatibility

As long as there is no compatible sjbr/static-info-tables by the vendor a fork
fork is provided at https://github.com/garbast/static_info_tables. To make use
of it add the following to your project composer.json.

```
"repositories": [
    {
        "url": "https://github.com/garbast/static_info_tables.git",
        "type": "git"
    }
],
```
