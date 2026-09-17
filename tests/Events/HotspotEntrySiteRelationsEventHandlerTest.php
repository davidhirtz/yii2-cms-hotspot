<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Actions\PreloadEntrySiteRelations;
use Hirtz\Cms\Models\Block;
use Hirtz\Cms\Models\BlockAsset;
use Hirtz\Cms\Models\Section;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Cms\Models\Types\BlockSectionType;
use Hirtz\Cms\Models\Types\SectionType;
use Hirtz\Cms\Test\Models\TestEntry;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Db\ActiveQuery;
use Override;
use Yii;

class HotspotEntrySiteRelationsEventHandlerTest extends TestCase
{
    use HotspotFixtureTrait;

    #[Override]
    protected function tearDown(): void
    {
        Yii::$container->clear(Section::class);
        parent::tearDown();
    }

    public function testPreloadEntrySiteRelations(): void
    {
        $entry = $this->getEntryFromFixture('page-enabled');

        $preload = new PreloadEntrySiteRelations([
            'entry' => $entry,
        ]);

        $section = current($preload->entry->getRelatedRecords()['sections']);
        self::assertNotFalse($section);

        self::assertInstanceOf(Section::class, $section);
        self::assertEquals(1, $section->id);

        $asset = current($section->getRelatedRecords()['assets']);
        self::assertNotFalse($asset);

        self::assertInstanceOf(SectionAsset::class, $asset);
        self::assertEquals(4, $asset->id);

        self::assertArrayHasKey('hotspots', $asset->getRelatedRecords());

        $hotspot = current($asset->getRelatedRecords()['hotspots']);
        self::assertNotFalse($hotspot);

        self::assertInstanceOf(Hotspot::class, $hotspot);
        self::assertEquals(1, $hotspot->id);

        self::assertArrayHasKey('assets', $hotspot->getRelatedRecords());

        $asset = current($hotspot->getRelatedRecords()['assets']);
        self::assertNotFalse($asset);

        self::assertInstanceOf(HotspotAsset::class, $asset);
        self::assertEquals(8, $asset->id);
        self::assertSame($hotspot->id, $asset->model->id);

        self::assertArrayHasKey('file', $asset->getRelatedRecords());

        $file = $asset->getRelatedRecords()['file'];

        self::assertInstanceOf(File::class, $file);
        self::assertSame($asset->file_id, $file->id);
    }

    /**
     * A block's assets are loaded with the entry's own, so their hotspots are too (monorepo issue #145).
     */
    public function testTheHotspotsOfABlockAssetAreLoaded(): void
    {
        $module = Block::getModule();
        $module->enableBlocks = true;
        $module->enableBlockAssets = true;

        Yii::$container->set(Section::class, HotspotBlockSectionModel::class);
        ActiveQuery::setStatus(TestEntry::STATUS_ENABLED);

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

        $entry = $this->getEntryFromFixture('page-enabled');

        $section = HotspotBlockSectionModel::instantiateByType(HotspotBlockSectionModel::TYPE_BLOCK);
        $section->populateEntryRelation($entry);
        $section->populateBlockRelation(Block::findOne($block->id));
        self::assertTrue($section->insert(), print_r($section->getErrors(), true));

        $entry = $this->getEntryFromFixture('page-enabled');
        new PreloadEntrySiteRelations(['entry' => $entry]);

        $sections = array_filter($entry->sections, fn (Section $section): bool => (bool)$section->block_id);
        $section = reset($sections);
        self::assertNotFalse($section);

        $assets = $section->getVisibleAssets();
        self::assertCount(1, $assets);

        $loaded = reset($assets);
        self::assertInstanceOf(BlockAsset::class, $loaded);
        self::assertSame($asset->id, $loaded->id);
        self::assertArrayHasKey('hotspots', $loaded->getRelatedRecords());

        $hotspots = $loaded->getRelatedRecords()['hotspots'];
        self::assertCount(1, $hotspots);
        self::assertSame($hotspot->id, current($hotspots)->id);
    }
}

/**
 * A scratch section model, declared here because a second class in a test file is only autoloadable while that
 * file runs.
 */
class HotspotBlockSectionModel extends Section
{
    public const int TYPE_BLOCK = 2;

    #[Override]
    public function getTypes(): array
    {
        return [
            SectionType::make(self::TYPE_DEFAULT)
                ->name('Default'),
            BlockSectionType::make(self::TYPE_BLOCK),
        ];
    }
}
