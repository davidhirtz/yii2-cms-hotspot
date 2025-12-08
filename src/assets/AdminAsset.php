<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\assets;

use Hirtz\Skeleton\Assets\JuiAsset;
use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $css = ['css/hotspot.min.css'];

    public $depends = [
        \Hirtz\Skeleton\Assets\AdminAsset::class,
        JuiAsset::class,
    ];

    public $js = ['js/hotspot.min.js'];
    public $sourcePath = '@hotspot/assets/admin';
}
