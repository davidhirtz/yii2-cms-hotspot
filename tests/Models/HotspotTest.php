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

    public function testUnchangedCoordinatesAreNotDirty(): void
    {
        $hotspot = Hotspot::findOne(1);
        self::assertInstanceOf(Hotspot::class, $hotspot);

        $hotspot->load(['x' => (string)(float)$hotspot->x, 'y' => (string)(float)$hotspot->y], '');

        self::assertTrue($hotspot->validate());
        self::assertFalse($hotspot->isAttributeChanged('x'));
        self::assertFalse($hotspot->isAttributeChanged('y'));
    }

    public function testANamelessHotspotIsNamedByItsPosition(): void
    {
        $hotspot = Hotspot::findOne(1);
        self::assertInstanceOf(Hotspot::class, $hotspot);

        $hotspot->name = null;

        self::assertSame(
            Yii::t('skeleton', 'COMMON_MODEL_ID', ['model' => $hotspot->getAdminType(), 'id' => 1]),
            $hotspot->getAdminName(),
        );

        // The name is read per row (trail, search), so only the header's subtitle names the total.
        self::assertSame(
            Yii::t('skeleton', 'COMMON_MODEL_POSITION_TOTAL', [
                'model' => $hotspot->getAdminType(),
                'position' => 1,
                'total' => 2,
            ]),
            $hotspot->getAdminSubtitle(),
        );

        $hotspot->name = 'Test Hotspot';
        self::assertSame('Test Hotspot', $hotspot->getAdminName());
    }

    public function testAPositionPastTheCountFallsBackToTheNumber(): void
    {
        $hotspot = Hotspot::findOne(1);
        self::assertInstanceOf(Hotspot::class, $hotspot);

        $hotspot->position = 42;

        self::assertSame(
            Yii::t('skeleton', 'COMMON_MODEL_ID', ['model' => $hotspot->getAdminType(), 'id' => 42]),
            $hotspot->getAdminSubtitle(),
        );
    }

    public function testDeletingAHotspotRenumbersTheOthers(): void
    {
        $hotspot = Hotspot::findOne(1);
        self::assertInstanceOf(Hotspot::class, $hotspot);
        self::assertSame(1, $hotspot->delete());

        $sibling = Hotspot::findOne(2);
        self::assertInstanceOf(Hotspot::class, $sibling);
        self::assertSame(1, $sibling->position);
        self::assertSame(1, $sibling->asset->getAttribute('hotspot_count'));
    }
}
