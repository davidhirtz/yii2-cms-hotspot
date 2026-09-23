<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Navs\HotspotSubmenu;
use Hirtz\Cms\Hotspot\Test\Fixtures\HotspotFixture;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Test\Fixtures\AssetFixture;
use Override;

class HotspotSubmenuTest extends TestCase
{
    use HotspotFixtureTrait;

    /**
     * Declared rather than merged: both fixture traits carry a `fixtures()` of their own.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            ...$this->cmsFixtures(),
            'asset' => [
                'class' => AssetFixture::class,
                'dataFile' => '@hotspot/Test/Fixtures/Data/asset.php',
            ],
            'hotspot' => HotspotFixture::class,
        ];
    }

    /**
     * A hotspot is listed on its asset's page rather than in an index of its own, so that is the way back.
     */
    public function testTheBackButtonLeadsToTheAsset(): void
    {
        $hotspot = Hotspot::findOne(1);

        $html = HotspotSubmenu::make()
            ->model($hotspot)
            ->render();

        self::assertStringContainsString('<a class="nav-link nav-back-link" href="/admin/cms/section-asset/update?id=' . $hotspot->asset_id . '"', $html);
        self::assertStringContainsString('/admin/hotspot/hotspot/update?id=' . $hotspot->id, $html);
    }
}
