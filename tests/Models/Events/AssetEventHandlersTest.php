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
use Hirtz\Media\Models\File;

/**
 * The cms knows nothing of hotspots, so they follow their asset through the delete and the duplicate by events.
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
     * The hotspot's own asset shows the same file, so deleting the section asset removes it before the file reaches
     * it (monorepo issue #568).
     */
    public function testDeletingAFileShownByAnAssetAndItsHotspotDeletesBoth(): void
    {
        HotspotAsset::updateAll(['file_id' => 3], ['id' => 8]);

        $file = File::findOne(3);

        self::assertNotNull($file);
        self::assertSame(1, $file->delete());

        self::assertNull(SectionAsset::findOne(4));
        self::assertNull(HotspotAsset::findOne(8));
        self::assertSame(0, (int)Hotspot::find()->where(['asset_id' => 4])->count());
    }

    /**
     * `hotspot.asset_id` cascades, so only the hotspot's own assets prove the handler ran (monorepo issue #145).
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
     * A section holds a file once, so the duplicate goes to another section, as in `DuplicateSection`.
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
