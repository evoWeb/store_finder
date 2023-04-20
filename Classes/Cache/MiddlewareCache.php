<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Cache;

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class MiddlewareCache
{
    protected FrontendInterface $frontendInterface;

    protected FrontendUserAuthentication $frontendUser;

    public function __construct(FrontendInterface $frontendInterface, FrontendUserAuthentication $frontendUser)
    {
        $this->frontendInterface = $frontendInterface;
        $this->frontendUser = $frontendUser;
    }

    public function writeCache(string $cacheIdentifier, array $values)
    {
        $this->frontendInterface->set($cacheIdentifier, $values);
    }

    public function readCache(string $cacheIdentifier)
    {
        return $this->frontendInterface->get($cacheIdentifier);
    }
}
