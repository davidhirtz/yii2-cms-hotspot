<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot;

use Hirtz\Cms\Models\BlockAsset;
use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Media\Models\Interfaces\AssetInterface;
use Hirtz\Skeleton\Models\Interfaces\VisibleAttributeInterface;

class Module extends \Hirtz\Skeleton\Base\Module
{
    /**
     * Named among an asset type's hidden fields to take its hotspots off, since this bundle owns neither the asset
     * classes nor their type.
     */
    final public const string FIELD_HOTSPOTS = 'hotspots';

    public bool $enableEntryAssetHotspots = false;

    /**
     * A block renders in place of a section, so its assets follow this flag.
     */
    public bool $enableSectionAssetHotspots = true;

    public bool $enableHotspotAssets = true;

    /**
     * The single reader of the flags and the type's marker, so the admin and the frontend cannot disagree.
     */
    public function allowsHotspots(AssetInterface $asset): bool
    {
        if ($asset instanceof VisibleAttributeInterface && !$asset->isAttributeVisible(self::FIELD_HOTSPOTS)) {
            return false;
        }

        if ($asset instanceof EntryAsset) {
            return $this->enableEntryAssetHotspots;
        }

        return ($asset instanceof SectionAsset || $asset instanceof BlockAsset) && $this->enableSectionAssetHotspots;
    }
}
