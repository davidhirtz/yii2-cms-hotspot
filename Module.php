<?php

namespace davidhirtz\yii2\hotspot;

use davidhirtz\yii2\skeleton\modules\ModuleTrait;

/**
 * Class Module
 * @package davidhirtz\yii2\hotspot
 */
class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @var bool whether hotspots should have assets
     */
    public $enableHotspotAssets = true;

    /**
     * @var int the default hotspot type which is applied to all default admin urls
     */
    public $defaultHotspotType;
}