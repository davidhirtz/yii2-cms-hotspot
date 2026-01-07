<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Test\Fixtures\Traits\FixtureTrait;
use Hirtz\Cms\Test\TestCase;

class HotspotTest extends TestCase
{
    use FixtureTrait;

    public function testCreateAndDeleteHotspot(): void
    {
        $asset = $this->getAssetFromFixture('post-asset');

        $hotspot = Hotspot::create();
        $hotspot->populateAssetRelation($asset);

        self::assertFalse($hotspot->insert());
        self::assertArrayHasKey('x', $hotspot->getErrors());
        self::assertArrayHasKey('y', $hotspot->getErrors());

        $hotspot->x = 50;
        $hotspot->y = 50;

        self::assertTrue($hotspot->insert());
        self::assertEquals(1, $asset->getAttribute('hotspot_count'));

        self::assertTrue($hotspot->delete() === 1);
        self::assertEquals(0, $asset->getAttribute('hotspot_count'));
    }
}
