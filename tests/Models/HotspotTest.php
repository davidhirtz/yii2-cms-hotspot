<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Yii;

class HotspotTest extends TestCase
{
    use HotspotFixtureTrait;

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

    public function testANamelessHotspotIsNamedByItsPosition(): void
    {
        $hotspot = Hotspot::findOne(1);
        self::assertInstanceOf(Hotspot::class, $hotspot);

        $hotspot->name = null;
        $hotspot->position = 42;

        self::assertSame(
            Yii::t('skeleton', 'COMMON_MODEL_ID', ['model' => $hotspot->getAdminType(), 'id' => 42]),
            $hotspot->getAdminName(),
        );

        // The name and the subtitle name the same record, so they must not disagree about how.
        self::assertSame($hotspot->getAdminSubtitle(), $hotspot->getAdminName());

        $hotspot->name = 'Test Hotspot';
        self::assertSame('Test Hotspot', $hotspot->getAdminName());
    }
}
