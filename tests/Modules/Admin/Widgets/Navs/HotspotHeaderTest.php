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

    public function testTheHotspotPageIsTitledWithTheHotspot(): void
    {
        $this->login();
        $hotspot = Hotspot::findOne(1);

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString('>' . $hotspot->getAdminName() . '</a></h1>', $html);
    }

    public function testTheHotspotPathHoldsTheAssetTheSectionAndTheEntry(): void
    {
        $this->login();
        $asset = Hotspot::findOne(1)->asset;
        $section = $asset->model;
        self::assertInstanceOf(Section::class, $section);

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString(
            '<a class="header-path-link" href="/admin/cms/entry/update?id=' . $section->entry_id . '">',
            $html,
        );
        self::assertStringContainsString(
            '<a class="header-path-link" href="/admin/cms/section/update?id=' . $asset->model_id . '">',
            $html,
        );
        self::assertStringContainsString(
            '<a class="header-path-link" href="/admin/cms/section-asset/update?id=' . $asset->id . '">',
            $html,
        );
    }

    /**
     * The path is capped at three, so the entry collapses into the non-linked `…`; the bar carries all four.
     */
    public function testTheHotspotAssetPathHoldsTheThreeNearestAncestorsAndAnEllipsis(): void
    {
        $this->login();
        $asset = Asset::findOne(8);
        $hotspot = Hotspot::findOne(1);
        $sectionAsset = $hotspot->asset;
        $section = $sectionAsset->model;
        self::assertInstanceOf(Section::class, $section);

        $html = Yii::$app->runAction('admin/hotspot/hotspot-asset/update', ['id' => $asset->id]);

        self::assertIsString($html);
        self::assertStringContainsString('<li class="header-path-item">…</li>', $html);
        self::assertStringContainsString(
            '<a class="header-path-link" href="/admin/cms/section/update?id=' . $section->id . '">',
            $html,
        );
        self::assertStringContainsString(
            '<a class="header-path-link" href="/admin/cms/section-asset/update?id=' . $sectionAsset->id . '">',
            $html,
        );
        self::assertStringContainsString(
            '<a class="header-path-link" href="/admin/hotspot/hotspot/update?id=' . $hotspot->id . '">',
            $html,
        );

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
