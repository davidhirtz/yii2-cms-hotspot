<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\assets;

use Hirtz\Skeleton\assets\JuiAsset;
use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $css = ['css/hotspot.min.css'];

    public $depends = [
        \Hirtz\Skeleton\assets\AdminAsset::class,
        JuiAsset::class,
    ];

    public $js = ['js/hotspot.min.js'];
    public $sourcePath = '@hotspot/assets/admin';
}
