<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules;

use Hirtz\Cms\Hotspot\Module;
use Yii;

trait ModuleTrait
{
    public static function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('hotspot');
        return $module;
    }
}
