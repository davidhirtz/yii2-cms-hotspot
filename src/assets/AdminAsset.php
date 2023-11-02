<?php

namespace davidhirtz\yii2\cms\hotspot\assets;

use davidhirtz\yii2\skeleton\assets\JuiAsset;
use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $css = ['css/hotspot.min.css'];

    public $depends = [
        \davidhirtz\yii2\skeleton\assets\AdminAsset::class,
        JuiAsset::class,
    ];

    public $js = ['js/hotspot.min.js'];
    public $sourcePath = '@hotspot/assets/admin';
}
