<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Test\Models\TestEntry;
use Hirtz\Skeleton\Db\DateTime;

/**
 * The fixture hotspots sit on a section asset of entry 1, whose `updated_at` is its sitemap `lastmod`.
 */
class HotspotEntryUpdatedAtTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testAddingAHotspotTouchesTheEntry(): void
    {
        $aged = $this->ageEntry();

        $hotspot = Hotspot::create();
        $hotspot->populateAssetRelation($this->getAssetFromFixture('section-image-1'));
        $hotspot->x = 30;
        $hotspot->y = 30;

        self::assertTrue($hotspot->insert(), print_r($hotspot->getErrors(), true));
        $this->assertEntryTouched($aged);
    }

    public function testUpdatingAHotspotTouchesTheEntry(): void
    {
        $aged = $this->ageEntry();

        $hotspot = $this->getHotspotFromFixture('hotspot-1');
        $hotspot->x = 30;

        self::assertSame(1, $hotspot->update(false));
        $this->assertEntryTouched($aged);
    }

    public function testDeletingAHotspotTouchesTheEntry(): void
    {
        $aged = $this->ageEntry();

        self::assertSame(1, $this->getHotspotFromFixture('hotspot-2')->delete());
        $this->assertEntryTouched($aged);
    }

    public function testAddingAHotspotAssetTouchesTheEntry(): void
    {
        $aged = $this->ageEntry();

        $asset = HotspotAsset::create();
        $asset->populateModelRelation($this->getHotspotFromFixture('hotspot-2'));
        $asset->populateFileRelation($this->getFileFromFixture('file-1'));

        self::assertTrue($asset->insert(), print_r($asset->getErrors(), true));
        $this->assertEntryTouched($aged);
    }

    public function testUpdatingAHotspotAssetTouchesTheEntry(): void
    {
        $aged = $this->ageEntry();

        $asset = HotspotAsset::findOne(8);
        self::assertNotNull($asset);

        $asset->status = HotspotAsset::STATUS_DISABLED;

        self::assertSame(1, $asset->update(false));
        $this->assertEntryTouched($aged);
    }

    public function testDeletingAHotspotAssetTouchesTheEntry(): void
    {
        $aged = $this->ageEntry();

        $asset = HotspotAsset::findOne(8);
        self::assertNotNull($asset);

        self::assertSame(1, $asset->delete());
        $this->assertEntryTouched($aged);
    }

    private function ageEntry(): int
    {
        $aged = new DateTime('-1 hour');
        TestEntry::updateAll(['updated_at' => $aged], ['id' => 1]);

        return $aged->getTimestamp();
    }

    private function assertEntryTouched(int $aged): void
    {
        self::assertGreaterThan($aged, TestEntry::findOne(1)?->updated_at?->getTimestamp());
    }
}
