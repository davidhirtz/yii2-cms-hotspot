<?php

namespace davidhirtz\yii2\hotspot\modules\admin;

use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package davidhirtz\yii2\hotspot\modules\admin
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
{
    /**
     * @var array
     */
    protected $defaultControllerMap = [
        'hotspot' => [
            'class' => 'davidhirtz\yii2\hotspot\modules\admin\controllers\HotspotController',
            'viewPath' => '@hotspot/modules/admin/views/hotspot',
        ],
        'hotspot-asset' => [
            'class' => 'davidhirtz\yii2\hotspot\modules\admin\controllers\HotspotAssetController',
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