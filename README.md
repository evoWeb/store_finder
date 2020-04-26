# TYPO3 Extension ``store_finder``
[![Build Status](https://travis-ci.org/evoWeb/store_finder.svg?branch=master)](https://travis-ci.org/evoWeb/store_finder)
[![Latest Stable Version](https://poser.pugx.org/evoweb/store-finder/v/stable)](https://packagist.org/packages/evoweb/store-finder)
[![Monthly Downloads](https://poser.pugx.org/evoweb/store-finder/d/monthly)](https://packagist.org/packages/evoweb/store-finder)
[![Total Downloads](https://poser.pugx.org/evoweb/store-finder/downloads)](https://packagist.org/packages/evoweb/store-finder)

## Installation

### via Composer

The recommended way to install TYPO3 Console is by using [Composer](https://getcomposer.org):

    composer require evoweb/store-finder

### Installation from TYPO3 Extension Repository

Download and install the extension with the extension manager module or directly from the
[TER](https://typo3.org/extensions/repository/view/store_finder).

## TYPO3 10.x compatibility

As long as there is no compatible sjbr/static-info-tables by the vendor, a fork
will be provided at https://github.com/garbast/static_info_tables. To make use of
it add the following to your project composer.json.

```
"repositories": [
    {
        "url": "https://github.com/garbast/static_info_tables.git",
        "type": "git"
    }
],
```
