<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Controllers;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Media\Modules\Admin\Controllers\AbstractAssetController;
use Override;

class HotspotAssetController extends AbstractAssetController
{
    protected array $assetClasses = [
        HotspotAsset::class,
    ];

    /**
     * A hotspot asset borrows the permissions of the cms asset its hotspot sits on, and the coarse gate cannot know
     * which of the two that is.
     */
    #[Override]
    protected function getPermissionNames(string $action): array
    {
        return [
            EntryAsset::instance()->getPermissionName($action),
            SectionAsset::instance()->getPermissionName($action),
        ];
    }
}
