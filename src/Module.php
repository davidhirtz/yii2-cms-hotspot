<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot;

class Module extends \Hirtz\Skeleton\Base\Module
{
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
}
