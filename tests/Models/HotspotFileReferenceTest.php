<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Media\Models\CustomAttributes\FileCustomAttribute;
use Hirtz\Media\Models\FileReference;
use Override;
use Yii;

/**
 * An asset deletes its hotspots through their models, so their file references go with them.
 */
class HotspotFileReferenceTest extends TestCase
{
    use HotspotFixtureTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Yii::$container->set(Hotspot::class, [
            'customAttributes' => [FileCustomAttribute::make('icon')],
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        Yii::$container->clear(Hotspot::class);
        parent::tearDown();
    }

    public function testDeletingTheAssetDeletesTheReferencesOfItsHotspots(): void
    {
        $asset = $this->getAssetFromFixture('post-asset');

        $hotspot = Hotspot::create();
        $hotspot->populateAssetRelation($asset);
        $hotspot->x = 50;
        $hotspot->y = 50;
        $hotspot->setAttribute('icon', $asset->file_id);

        self::assertTrue($hotspot->insert(), print_r($hotspot->getErrors(), true));
        self::assertTrue($this->hasReference($hotspot));

        self::assertSame(1, $asset->delete());
        self::assertFalse($this->hasReference($hotspot));
    }

    private function hasReference(Hotspot $hotspot): bool
    {
        return FileReference::find()
            ->where(['model_class' => $hotspot::class, 'model_id' => $hotspot->id])
            ->exists();
    }
}
