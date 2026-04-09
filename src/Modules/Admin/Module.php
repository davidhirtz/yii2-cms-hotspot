<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin;

use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotController;
use Override;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module
{
    /**
     * @var bool whether entry assets should have hotspots enabled, defaults to `false`.
     */
    public bool $enableEntryAssetHotspots = false;

    /**
     * @var bool whether entry assets should have hotspots enabled, defaults to `true`.
     */
    public bool $enableSectionAssetHotspots = true;

    /**
     * @var bool whether hotspots should have assets enabled, defaults to `true`.
     */
    public bool $enableHotspotAssets = true;

    protected array $defaultControllerMap = [
        'hotspot' => HotspotController::class,
        'hotspot-asset' => HotspotAssetController::class,
    ];

    #[Override]
    public function init(): void
    {
        $this->controllerMap = [...$this->defaultControllerMap, ...$this->controllerMap];
        parent::init();
    }
}
