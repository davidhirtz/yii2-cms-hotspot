<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Models;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;

class HotspotAssetTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testItDeclaresFewerDefinitionsThanAnAsset(): void
    {
        $asset = $this->getHotspotAsset();

        self::assertSame(
            ['name', 'content', 'alt_text', 'link'],
            array_keys($asset->getCustomAttributeDefinitions())
        );
    }

    /**
     * A renderer asks every asset for these, so dropping the definitions must not turn the read into an error.
     */
    public function testTheDroppedDefinitionsRenderAsNothing(): void
    {
        $asset = $this->getHotspotAsset();

        self::assertNull($asset->getLoading());
        self::assertNull($asset->getFetchPriority());
        self::assertSame('', $asset->getFormattedEmbedUrl());
        self::assertNull($asset->getVisibleAttribute('embed_url'));
    }

    /**
     * The values the record still has are unaffected, and a key no definition claims survives a save.
     */
    public function testAValueOfADroppedDefinitionIsKept(): void
    {
        $asset = $this->getHotspotAsset();
        $asset->setAttribute('custom_attributes', ['embed_url' => 'https://example.com/embed']);

        $asset->name = 'A name';

        self::assertSame(1, $asset->update(), implode(' ', $asset->getErrorSummary(true)));

        $stored = HotspotAsset::findOne($asset->id)->getAttribute('custom_attributes');

        self::assertSame('https://example.com/embed', $stored['embed_url']);
        self::assertSame('A name', $stored['name']);
    }

    public function testAnAssetOfAnotherModelKeepsThemAll(): void
    {
        $asset = $this->getAssetFromFixture('entry-asset');

        self::assertSame(
            ['name', 'content', 'alt_text', 'link', 'embed_url', 'loading', 'fetchpriority'],
            array_keys($asset->getCustomAttributeDefinitions())
        );
    }

    private function getHotspotAsset(): HotspotAsset
    {
        $asset = HotspotAsset::findOne($this->getAssetFixtureData('hotspot-asset-1-1')['id']);
        self::assertInstanceOf(HotspotAsset::class, $asset);

        return $asset;
    }
}
