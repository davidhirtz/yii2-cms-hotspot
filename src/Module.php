<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot;

use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Media\Models\Interfaces\AssetInterface;
use Hirtz\Skeleton\Models\Interfaces\VisibleAttributeInterface;

class Module extends \Hirtz\Skeleton\Base\Module
{
    /**
     * The marker that takes the hotspots off an asset type, named among its hidden fields. The cms owns the asset
     * classes and the media bundle owns their type, so this bundle has no `allowHotspots()` of its own to add to
     * either — a project declaring its own panel on a type it does not own does the same.
     */
    final public const string FIELD_HOTSPOTS = 'hotspots';

    /**
     * @var bool whether entry assets should have hotspots enabled, defaults to `false`.
     */
    public bool $enableEntryAssetHotspots = false;

    /**
     * @var bool whether section assets should have hotspots enabled, defaults to `true`.
     */
    public bool $enableSectionAssetHotspots = true;

    /**
     * @var bool whether hotspots should have assets enabled, defaults to `true`.
     */
    public bool $enableHotspotAssets = true;

    /**
     * Whether the asset carries hotspots: the installation's flag for the kind of asset it is, then the asset
     * type's own marker. The single reader, so the admin and the frontend cannot disagree.
     */
    public function allowsHotspots(AssetInterface $asset): bool
    {
        if ($asset instanceof VisibleAttributeInterface && !$asset->isAttributeVisible(self::FIELD_HOTSPOTS)) {
            return false;
        }

        if ($asset instanceof EntryAsset) {
            return $this->enableEntryAssetHotspots;
        }

        return $asset instanceof SectionAsset && $this->enableSectionAssetHotspots;
    }
}
