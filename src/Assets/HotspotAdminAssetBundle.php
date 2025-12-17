<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Assets;

use Hirtz\Skeleton\Assets\AdminAssetBundle;
use yii\web\AssetBundle;

class HotspotAdminAssetBundle extends AssetBundle
{
    public $css = ['css/hotspot.min.css'];

    public $depends = [
        AdminAssetBundle::class,
    ];

    public $js = ['js/hotspot.min.js'];
    public $sourcePath = '@hotspot/../resources/assets/admin';
}
