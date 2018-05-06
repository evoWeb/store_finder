<?php
namespace Evoweb\StoreFinder\Utility;

/**
 * This file is developed by evoweb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Provide a way to get the configuration just everywhere
 */
class ExtensionConfigurationUtility implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var array
     */
    protected static $configuration;

    public static function getConfiguration(): array
    {
        if (self::$configuration === null) {
            if (class_exists(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)) {
                self::$configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
                )->get('store_finder');
            } else {
                self::$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['store_finder']);
            }
        }

        return is_array(self::$configuration) ? self::$configuration : [];
    }
}
