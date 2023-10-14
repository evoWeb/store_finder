<?php

declare(strict_types=1);

namespace Evoweb\StoreFinder\Service;

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

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class FrontendUserService
{
    public static function getCurrentUser(): FrontendUserAuthentication
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        return $request->getAttribute('frontend.user');
    }
}
