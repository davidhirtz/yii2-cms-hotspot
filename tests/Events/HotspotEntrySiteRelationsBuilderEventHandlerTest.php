<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Events;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Asset;
use Hirtz\Cms\Models\Builders\EntrySiteRelationsBuilder;
use Hirtz\Cms\Models\Section;

class HotspotEntrySiteRelationsBuilderEventHandlerTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testEntrySiteRelationsBuilder(): void
    {
        $entry = $this->getEntryFromFixture('page-enabled');

        $builder = new EntrySiteRelationsBuilder([
            'entry' => $entry,
        ]);

        $section = current($builder->entry->getRelatedRecords()['sections']);

        self::assertInstanceOf(Section::class, $section);
        self::assertEquals(1, $section->id);

        $asset = current($section->getRelatedRecords()['assets']);

        self::assertInstanceOf(Asset::class, $asset);
        self::assertEquals(4, $asset->id);

        self::assertArrayHasKey('hotspots', $asset->getRelatedRecords());

        $hotspot = current($asset->getRelatedRecords()['hotspots']);

        self::assertInstanceOf(Hotspot::class, $hotspot);
        self::assertEquals(1, $hotspot->id);

        self::assertArrayHasKey('assets', $hotspot->getRelatedRecords());

        $asset = current($hotspot->getRelatedRecords()['assets']);

        self::assertInstanceOf(HotspotAsset::class, $asset);
        self::assertEquals(1, $asset->id);
    }
}
