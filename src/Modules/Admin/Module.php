<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin;

use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotAssetController;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\HotspotController;
use Override;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
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
        'hotspot' => [
            'class' => HotspotController::class,
            'viewPath' => '@hotspot/../resources/views/admin/hotspot',
        ],
        'hotspot-asset' => [
            'class' => HotspotAssetController::class,
            'viewPath' => '@hotspot/../resources/views/admin/hotspot-asset',
        ],
    ];

    #[Override]
    public function init(): void
    {
        $this->module->controllerMap = ArrayHelper::merge(
            $this->module->controllerMap,
            $this->defaultControllerMap,
            $this->controllerMap,
        );

        parent::init();
    }
}
