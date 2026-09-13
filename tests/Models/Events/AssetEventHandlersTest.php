<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Media\Models\Actions\DuplicateAsset;

/**
 * The hotspots of a cms asset are the bundle's own, so the cms bootstrap knows nothing about them: they follow
 * their asset through the delete and the duplicate through events.
 */
class AssetEventHandlersTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testDeletingTheAssetDeletesItsHotspots(): void
    {
        $asset = SectionAsset::findOne(4);

        self::assertNotNull($asset);
        self::assertSame(2, $asset->getAttribute('hotspot_count'));

        self::assertSame(1, $asset->delete());

        self::assertSame(0, (int)Hotspot::find()->where(['asset_id' => 4])->count());
    }

    public function testDuplicatingTheAssetCopiesItsHotspots(): void
    {
        $asset = SectionAsset::findOne(4);
        $duplicate = DuplicateAsset::create(['asset' => $asset]);

        self::assertEmpty($duplicate->getErrors());
        self::assertNotSame($asset->id, $duplicate->id);

        self::assertSame(2, $duplicate->getAttribute('hotspot_count'));

        $hotspots = Hotspot::find()
            ->where(['asset_id' => $duplicate->id])
            ->orderBy(['position' => SORT_ASC])
            ->all();

        self::assertCount(2, $hotspots);
        self::assertSame('Test Hotspot 1', $hotspots[0]->name);
        self::assertSame('Test Hotspot 2', $hotspots[1]->name);

        // The hotspot's own assets come along, so the duplicated image keeps its markers intact.
        self::assertSame(1, $hotspots[0]->asset_count);
    }
}
