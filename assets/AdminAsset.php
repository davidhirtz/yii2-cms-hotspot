<?php

namespace davidhirtz\yii2\hotspot\assets;

use yii\web\AssetBundle;

/**
 * Class AdminAsset
 * @package davidhirtz\yii2\hotspot\assets
 */
class AdminAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@hotspot/assets/admin';

    /**
     * @var array
     */
    public $css = [
        YII_DEBUG ? 'css/admin.css' : 'css/admin.min.css',
    ];
    /**
     * @var array
     */
    public $js = [
        YII_DEBUG ? 'js/admin.js' : 'js/admin.min.js',
    ];

    /**
     * @var array
     */
    public $depends = [
        'davidhirtz\yii2\skeleton\assets\AdminAsset',
        'davidhirtz\yii2\skeleton\assets\JuiAsset',
    ];
}
