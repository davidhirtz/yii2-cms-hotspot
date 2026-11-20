<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Media\Models\File;

class HotspotAssetTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testThePermissionIsTheHotspotAssetsOwners(): void
    {
        $asset = HotspotAsset::findOne(8);

        self::assertNotNull($asset);
        self::assertSame($asset->model->asset->getPermissionName(), $asset->getPermissionName());
    }

    public function testAnAssetWhoseHotspotIsGoneIsLeftToTheFiles(): void
    {
        $asset = HotspotAsset::create();
        $asset->model_class = Hotspot::class;
        $asset->model_id = (int)Hotspot::find()->max('id') + 1;

        self::assertSame(File::AUTH_FILE, $asset->getPermissionName());
    }
}
