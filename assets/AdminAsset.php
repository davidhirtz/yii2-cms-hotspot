<?php

namespace davidhirtz\yii2\annotation\assets;

use yii\web\AssetBundle;

/**
 * Class AdminAsset
 * @package davidhirtz\yii2\annotation\assets
 */
class AdminAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@annotation/assets/admin';

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
        'davidhirtz\yii2\annotation\assets\AnnotoriousAssetBundle',
    ];
}
