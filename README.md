# TYPO3 Extension ``store_finder``

![build](https://github.com/evoWeb/store_finder/workflows/build/badge.svg?branch=develop)
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

## Caching

The frontend middlewares are heavily cached and for every change in a location or category record the cache needs to be cleared to see changes. To counter this problem you can add a snippet to the sites TCEMAIN.tsconfig.

```
[traverse(page, "uid") == 70]
  TCEMAIN {
    clearCacheCmd = all
  }
[end]
```
With the condition at the top, we can make sure that only a certain page or folder is affected by the automatic cache clearing. Please see [TYPO3 Documentation](https://docs.typo3.org/m/typo3/reference-tsconfig/main/en-us/PageTsconfig/TceMain.html#clearcachecmd) for more information.
