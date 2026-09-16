<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Cms\Models\Actions\PreloadEntrySiteRelations;
use Hirtz\Cms\Models\Section;
use Hirtz\Media\Models\File;

class HotspotEntrySiteRelationsEventHandlerTest extends TestCase
{
    use HotspotFixtureTrait;

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
}
