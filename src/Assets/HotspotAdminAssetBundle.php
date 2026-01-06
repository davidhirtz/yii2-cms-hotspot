<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Assets;

use Hirtz\Skeleton\Assets\AdminAssetBundle;
use yii\web\AssetBundle;

class HotspotAdminAssetBundle extends AssetBundle
{
    public $css = ['css/hotspot.css'];
    public $depends = [AdminAssetBundle::class];
    public $jsOptions = ['type' => 'module'];
    public $sourcePath = '@hotspot/../resources/assets/dist';

    public function getModuleFilename(): string
    {
        return "$this->baseUrl/js/hotspot.js";
    }
}
