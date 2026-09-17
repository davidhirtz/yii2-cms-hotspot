<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Test\Fixtures\HotspotFixture;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Section;
use Hirtz\Cms\Test\Fixtures\AssetFixture;
use Hirtz\Media\Models\Asset;
use Hirtz\Skeleton\Models\Breadcrumb;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Skeleton\Web\View;
use Override;
use Yii;

class HotspotHeaderTest extends TestCase
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
            'user' => UserFixture::class,
        ];
    }

    public function testTheHotspotPageRendersTheActionDropdown(): void
    {
        $this->login();
        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString('dropdown-actions', $html);
        self::assertStringContainsString('/admin/hotspot/hotspot/delete?id=1', $html);
    }

    public function testTheHotspotAssetPageRendersTheActionDropdown(): void
    {
        $this->login();
        $html = Yii::$app->runAction('admin/hotspot/hotspot-asset/index', ['hotspot' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString('dropdown-actions', $html);
        self::assertStringContainsString('/admin/hotspot/hotspot-asset/create?hotspot=1', $html);
    }

    public function testTheHotspotPageIsTitledWithTheEntry(): void
    {
        $this->login();
        $section = Hotspot::findOne(1)->asset->model;
        self::assertInstanceOf(Section::class, $section);

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString(
            '<h1><a href="/admin/cms/entry/update?id=' . $section->entry_id . '">'
            . $section->entry->getAdminName() . '</a></h1>',
            $html,
        );
    }

    public function testTheHotspotSubtitleHoldsTheSectionTheAssetAndTheHotspot(): void
    {
        $this->login();
        $hotspot = Hotspot::findOne(1);
        $asset = $hotspot->asset;
        $section = $asset->model;
        self::assertInstanceOf(Section::class, $section);

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertSame(
            [
                $this->subtitleItem("/admin/cms/section/update?id=$section->id", $section->getAdminType(), $section->position),
                $this->subtitleItem("/admin/cms/section-asset/update?id=$asset->id", $this->getAssetType(), $asset->position),
                $this->subtitleItem("/admin/hotspot/hotspot/update?id=$hotspot->id", $hotspot->getAdminType(), $hotspot->position),
            ],
            $this->getSubtitleItems($html),
        );
    }

    /**
     * An asset four levels down still reads as one line under the entry's title; the bar carries the records.
     */
    public function testTheHotspotAssetSubtitleHoldsTheWholeChain(): void
    {
        $this->login();
        $asset = Asset::findOne(8);
        $hotspot = Hotspot::findOne(1);
        $sectionAsset = $hotspot->asset;
        $section = $sectionAsset->model;
        self::assertInstanceOf(Section::class, $section);

        $html = Yii::$app->runAction('admin/hotspot/hotspot-asset/update', ['id' => $asset->id]);

        self::assertIsString($html);
        self::assertStringContainsString(
            '<h1><a href="/admin/cms/entry/update?id=' . $section->entry_id . '">'
            . $section->entry->getAdminName() . '</a></h1>',
            $html,
        );
        self::assertSame(
            [
                $this->subtitleItem("/admin/cms/section/update?id=$section->id", $section->getAdminType(), $section->position),
                $this->subtitleItem("/admin/cms/section-asset/update?id=$sectionAsset->id", $this->getAssetType(), $sectionAsset->position),
                $this->subtitleItem("/admin/hotspot/hotspot/update?id=$hotspot->id", $hotspot->getAdminType(), $hotspot->position),
                $this->subtitleItem("/admin/hotspot/hotspot-asset/update?id=$asset->id", $this->getAssetType(), $asset->position),
            ],
            $this->getSubtitleItems($html),
        );

        self::assertStringContainsString('#' . $section->getHtmlId() . '" target="_blank"', $html);

        self::assertSame(
            [
                Yii::t('cms', 'COMMON_ENTRIES'),
                $section->entry->getAdminName(),
                Yii::t('cms', 'COMMON_SECTIONS'),
                $section->getAdminName(),
                $section->getAttributeLabel('asset_count'),
                $sectionAsset->getAdminName(),
                $hotspot->getAdminName(),
                $hotspot->getAttributeLabel('asset_count'),
            ],
            $this->getBreadcrumbLabels(),
        );
    }

    /**
     * @return array{string, string}
     */
    private function subtitleItem(string $route, string $type, int $position): array
    {
        return [$route, Yii::t('skeleton', 'COMMON_MODEL_ID', ['model' => $type, 'id' => $position])];
    }

    /**
     * @return list<array{string, string}> the href and the text of each subtitle item, in order. Matched rather
     *     than spelled out: an item also carries the generated `view-transition-name` its group is matched by.
     */
    private function getSubtitleItems(string $html): array
    {
        preg_match_all('~<a[^>]*class="header-subtitle-item"[^>]*>[^<]*</a>~', $html, $matches);

        return array_map(static function (string $tag): array {
            preg_match('~href="([^"]*)"~', $tag, $href);
            preg_match('~>([^<]*)<~', $tag, $text);

            return [$href[1] ?? '', $text[1] ?? ''];
        }, $matches[0]);
    }

    /**
     * An asset names itself by the base noun, whatever subclass it is: its owner is named right before it.
     */
    private function getAssetType(): string
    {
        return Yii::t('media', 'ASSET_ASSET');
    }

    /**
     * @return list<string>
     */
    private function getBreadcrumbLabels(): array
    {
        $view = Yii::$app->getView();
        self::assertInstanceOf(View::class, $view);

        return array_values(array_map(
            static fn (Breadcrumb $breadcrumb): string => $breadcrumb->label,
            $view->getBreadcrumbs(),
        ));
    }

    private function login(): void
    {
        $this->getWebUser()->setIdentity(User::findOne(1));
    }
}
