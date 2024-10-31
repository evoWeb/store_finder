<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Evoweb\StoreFinder\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * @internal This class is only meant to be used within EXT:install.
 */
#[UpgradeWizard('storeFinderCTypeMigration')]
final class StoreFinderCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'storefinder_map' => 'storefinder_map',
            'storefinder_cached' => 'storefinder_cached',
            'storefinder_show' => 'storefinder_show',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrate "Store Finder" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return '
            The "Store Finder" plugin is now registered as content element. Update migrates existing
            records and backend user permissions.
        ';
    }
}
