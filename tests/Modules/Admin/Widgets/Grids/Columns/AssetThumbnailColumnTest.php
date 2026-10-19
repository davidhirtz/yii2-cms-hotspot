<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;

class AssetThumbnailColumnTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testTheHotspotCountPrecedesTheTypeIcon(): void
    {
        $asset = $this->getAssetFromFixture('section-image-2');
        $asset->setAttribute('hotspot_count', 3);

        $body = (string)AssetThumbnailColumn::make()->renderBody($asset, 0, 0);

        self::assertMatchesRegularExpression('#<div class="thumbnail-badges"><div class="badge">3</div><div class="badge"><span [^>]*fa-mobile-screen#', $body);
    }

    public function testTheHotspotCountIsShownWithoutATypeIcon(): void
    {
        $asset = $this->getAssetFromFixture('section-image-1');
        $asset->setAttribute('hotspot_count', 2);

        $body = (string)AssetThumbnailColumn::make()->renderBody($asset, 0, 0);

        self::assertStringContainsString('<div class="thumbnail-badges"><div class="badge">2</div></div>', $body);
    }
}
