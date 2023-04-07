<?php

namespace davidhirtz\yii2\cms\hotspot\assets;

use davidhirtz\yii2\skeleton\assets\JuiAsset;
use yii\web\AssetBundle;

/**
 * The hotspot admin asset bundle.
 */
class AdminAsset extends AssetBundle
{
    /**
     * @var array
     */
    public $css = ['css/hotspot.min.css'];

    /**
     * @var array
     */
    public $depends = [
        \davidhirtz\yii2\skeleton\assets\AdminAsset::class,
        JuiAsset::class,
    ];

    /**
     * @var array
     */
    public $js = ['js/hotspot.min.js'];

    /**
     * @var string
     */
    public $sourcePath = '@hotspot/assets/admin';
}
