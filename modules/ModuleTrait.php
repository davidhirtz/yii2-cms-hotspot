<?php

namespace davidhirtz\yii2\annotation\modules;

use davidhirtz\yii2\annotation\Module;
use Yii;

/**
 * Trait ModuleTrait
 * @package davidhirtz\yii2\annotation\components
 */
trait ModuleTrait
{
    /**
     * @var Module
     */
    protected static $_module;

    /**
     * @return Module
     */
    public static function getModule()
    {
        if (static::$_module === null) {
            static::$_module = Yii::$app->getModule('annotation');
        }

        return static::$_module;
    }
}