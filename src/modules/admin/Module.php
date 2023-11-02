<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin;

use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotAssetController;
use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotController;
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
            'viewPath' => '@hotspot/modules/admin/views/hotspot',
        ],
        'hotspot-asset' => [
            'class' => HotspotAssetController::class,
            'viewPath' => '@hotspot/modules/admin/views/hotspot-asset',
        ],
    ];

    public function init(): void
    {
        $this->module->controllerMap = ArrayHelper::merge(array_merge($this->module->controllerMap, $this->defaultControllerMap), $this->controllerMap);
        parent::init();
    }
}