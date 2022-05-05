<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin;

use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package davidhirtz\yii2\cms\hotspot\modules\admin
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
{
    /**
     * @var bool whether entry assets should have hotspots enabled, defaults to `false`.
     */
    public $enableEntryAssetHotspots = false;

    /**
     * @var bool whether entry assets should have hotspots enabled, defaults to `true`.
     */
    public $enableSectionAssetHotspots = true;

    /**
     * @var bool whether hotspots should have assets enabled, defaults to `true`.
     */
    public $enableHotspotAssets = true;

    /**
     * @var array
     */
    protected $defaultControllerMap = [
        'hotspot' => [
            'class' => 'davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotController',
            'viewPath' => '@hotspot/modules/admin/views/hotspot',
        ],
        'hotspot-asset' => [
            'class' => 'davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotAssetController',
            'viewPath' => '@hotspot/modules/admin/views/hotspot-asset',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->module->controllerMap = ArrayHelper::merge(array_merge($this->module->controllerMap, $this->defaultControllerMap), $this->controllerMap);
        parent::init();
    }
}