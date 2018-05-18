<?php
namespace Evoweb\StoreFinder\Service\Provider;

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

interface EncodeProviderInterface extends \TYPO3\CMS\Core\SingletonInterface
{
    public function encodeAddress(array $parameter, array $settings): array;
}
