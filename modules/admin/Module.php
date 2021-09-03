<?php

namespace davidhirtz\yii2\annotation\modules\admin;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package davidhirtz\yii2\annotation\modules\admin
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
{
    /**
     * @var string the module display name, defaults to "Annotations"
     */
    public $name;

    /**
     * @var array
     */
    protected $defaultControllerMap = [
        'annotation' => [
            'class' => 'davidhirtz\yii2\annotation\modules\admin\controllers\AnnotationController',
            'viewPath' => '@annotation/modules/admin/views/annotation',
        ],
        'annotation-asset' => [
            'class' => 'davidhirtz\yii2\annotation\modules\admin\controllers\AnnotationAssetController',
            'viewPath' => '@annotation/modules/admin/views/annotation-asset',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!$this->name) {
            $this->name = Yii::t('annotation', 'Annotations');
        }

        $this->module->controllerMap = ArrayHelper::merge(array_merge($this->module->controllerMap, $this->defaultControllerMap), $this->controllerMap);
        parent::init();
    }
}