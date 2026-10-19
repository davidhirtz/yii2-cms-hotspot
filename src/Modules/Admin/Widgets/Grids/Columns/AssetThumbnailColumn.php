<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Media\Models\Asset;
use Hirtz\Skeleton\Html\Div;
use Override;

/**
 * @template T of Asset
 * @extends \Hirtz\Media\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn<T>
 */
class AssetThumbnailColumn extends \Hirtz\Media\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn
{
    #[Override]
    protected function getThumbnailBadges(Asset $asset): array
    {
        $badges = parent::getThumbnailBadges($asset);
        $hotspotCount = $asset->getAttribute('hotspot_count');

        if ($hotspotCount) {
            array_unshift($badges, Div::make()
                ->class('badge')
                ->text((string)$hotspotCount));
        }

        return $badges;
    }
}
