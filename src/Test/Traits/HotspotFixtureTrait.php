<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Test\Traits;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\Fixtures\HotspotFixture;
use Hirtz\Cms\Test\Fixtures\AssetFixture;
use Hirtz\Cms\Test\Fixtures\Traits\CmsFixtureTrait;

trait HotspotFixtureTrait
{
    use CmsFixtureTrait {
        CmsFixtureTrait::fixtures as cmsFixtures;
    }

    public function fixtures(): array
    {
        return [
            ...$this->cmsFixtures(),
            // One table, one fixture: a second `ActiveFixture` on `asset` would truncate the cms rows.
            'asset' => [
                'class' => AssetFixture::class,
                'dataFile' => '@hotspot/Test/Fixtures/Data/asset.php',
            ],
            'hotspot' => HotspotFixture::class,
        ];
    }

    protected function getHotspotFixture(): HotspotFixture
    {
        /** @var HotspotFixture $fixture */
        $fixture = $this->getFixture('hotspot');
        return $fixture;
    }

    protected function getHotspotFixtureData(string $key): array
    {
        return $this->getHotspotFixture()->data[$key];
    }

    protected function getHotspotFromFixture(string $key): Hotspot
    {
        return Hotspot::findOne($this->getHotspotFixtureData($key)['id']);
    }
}
