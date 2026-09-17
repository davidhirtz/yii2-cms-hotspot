<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Block;
use Hirtz\Cms\Models\BlockAsset;
use Hirtz\Cms\Models\Section;
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

    /**
     * A block asset is subscribed like the other two cms assets (monorepo issue #145). `hotspot.asset_id` cascades,
     * so the hotspot row alone proves nothing — the hotspot's own assets are what the handler removes and the
     * database would leave behind.
     */
    public function testDeletingABlockAssetDeletesItsHotspots(): void
    {
        $module = Block::getModule();
        $module->enableBlocks = true;
        $module->enableBlockAssets = true;

        $block = Block::create();
        $block->name = 'Hotspot Block';
        self::assertTrue($block->insert(), print_r($block->getErrors(), true));

        $asset = BlockAsset::create();
        $asset->populateModelRelation($block);
        $asset->file_id = $this->getFileFixtureData('file-1')['id'];
        self::assertTrue($asset->insert(), print_r($asset->getErrors(), true));

        $hotspot = Hotspot::create();
        $hotspot->populateAssetRelation($asset);
        $hotspot->x = 10;
        $hotspot->y = 20;
        self::assertTrue($hotspot->insert(), print_r($hotspot->getErrors(), true));

        $hotspotAsset = HotspotAsset::create();
        $hotspotAsset->populateModelRelation($hotspot);
        $hotspotAsset->file_id = $this->getFileFixtureData('file-1')['id'];
        self::assertTrue($hotspotAsset->insert(), print_r($hotspotAsset->getErrors(), true));

        $asset = BlockAsset::findOne($asset->id);
        self::assertNotNull($asset);
        self::assertSame(1, $asset->getAttribute('hotspot_count'));

        self::assertSame(1, $asset->delete());
        self::assertNull(Hotspot::findOne($hotspot->id));
        self::assertNull(HotspotAsset::findOne($hotspotAsset->id));
    }

    /**
     * A section holds a file once, so the duplicate goes to another section — which is the only case left now that
     * the asset `duplicate` action is gone, and the one `DuplicateSection` and `DuplicateEntry` exercise.
     */
    public function testDuplicatingTheAssetCopiesItsHotspots(): void
    {
        $asset = SectionAsset::findOne(4);
        self::assertNotNull($asset);

        $section = Section::findOne(2);
        self::assertNotNull($section);

        $duplicate = DuplicateAsset::create(['asset' => $asset, 'model' => $section]);

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
