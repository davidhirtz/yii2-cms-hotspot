<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Widgets;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Hotspot\Widgets\Artwork;
use Hirtz\Cms\Models\Actions\PreloadEntrySiteRelations;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Skeleton\Helpers\FileHelper;
use Yii;

/**
 * A project that never wrote `widgets/_hotspots` got a 500 on every page with a hotspot (monorepo issue #309).
 */
class ArtworkTest extends TestCase
{
    use HotspotFixtureTrait;

    public function testAPageWithoutAHotspotViewGetsTheDefault(): void
    {
        $asset = $this->findAssetWithHotspots();
        $hotspot = current($asset->getRelatedRecords()['hotspots']);
        self::assertInstanceOf(Hotspot::class, $hotspot);

        // The view is resolved beside the page's, so the widget renders inside one.
        $page = Yii::getAlias('@runtime/artwork-test/page.php');
        FileHelper::createDirectory(dirname($page));
        file_put_contents($page, '<?= $artwork->renderTestHotspots() ?>');

        try {
            $html = Yii::$app->getView()->renderFile($page, [
                'artwork' => TestArtwork::make()->asset($asset)->hotspotViewFile('widgets/_missing'),
            ]);
        } finally {
            FileHelper::removeDirectory(dirname($page));
        }

        self::assertStringContainsString('id="' . $hotspot->getHtmlId() . '"', $html);
        self::assertStringContainsString('class="hotspot"', $html);
        self::assertStringContainsString("left: $hotspot->x%; top: $hotspot->y%", $html);
    }

    public function testNoViewRendersNoHotspots(): void
    {
        $asset = $this->findAssetWithHotspots();

        self::assertNull(TestArtwork::make()->asset($asset)->hotspotViewFile(false)->renderTestHotspots());
    }

    private function findAssetWithHotspots(): SectionAsset
    {
        $preload = new PreloadEntrySiteRelations(['entry' => $this->getEntryFromFixture('page-enabled')]);
        $section = current($preload->entry->getRelatedRecords()['sections']);
        self::assertNotFalse($section);

        $asset = current($section->getRelatedRecords()['assets']);
        self::assertInstanceOf(SectionAsset::class, $asset);

        return $asset;
    }
}

class TestArtwork extends Artwork
{
    public function renderTestHotspots(): ?string
    {
        return $this->renderHotspots();
    }
}
