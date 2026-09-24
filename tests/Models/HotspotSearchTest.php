<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Skeleton\Models\AdminModelChain;
use Hirtz\Skeleton\Models\User;

class HotspotSearchTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testTheDocumentCarriesTheContent(): void
    {
        $hotspot = $this->getHotspotFromFixture('hotspot-1');
        $document = $hotspot->getSearchDocuments()[0];

        self::assertSame('Test Hotspot 1', $document->title);
        self::assertStringContainsString('Test content for hotspot 1', $document->content);
    }

    public function testTheResultIsNamedLikeTheHeader(): void
    {
        // The owner sees every hit, so the result is not hidden by the asset's permission.
        $this->getWebUser()->setIdentity(User::findOne(['name' => 'owner']));

        $hotspot = $this->getHotspotFromFixture('hotspot-1');
        $result = $hotspot->getSearchResult();

        self::assertSame(AdminModelChain::fromModel($hotspot->asset)->base->getAdminName(), $result?->title);
        self::assertSame(
            [$hotspot->asset->getAdminSubtitle(), $hotspot->getAdminSubtitle()],
            array_slice($result->subtitles, -2)
        );
    }

    public function testTheSearchableQueryLoadsTheAsset(): void
    {
        $hotspot = Hotspot::findSearchable()->one();

        self::assertNotNull($hotspot);
        self::assertTrue($hotspot->isRelationPopulated('asset'));
    }

    public function testAHotspotAssetIsIndexedToo(): void
    {
        $asset = HotspotAsset::findOne(8);
        $asset->setAttributes([
            'content' => 'A caption on the marker',
            'alt_text' => 'A red bicycle',
        ], false);

        $document = $asset->getSearchDocuments()[0];

        self::assertStringContainsString('A caption on the marker', $document->content);
        self::assertStringContainsString('A red bicycle', $document->content);
    }
}
