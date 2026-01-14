<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Test\Fixtures;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use yii\test\ActiveFixture;

class HotspotAssetFixture extends ActiveFixture
{
    public $depends = [HotspotFixture::class];
    public $modelClass = HotspotAsset::class;
}
