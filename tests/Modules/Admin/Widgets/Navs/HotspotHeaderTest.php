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
        self::assertStringContainsString(
            '<h2 class="header-subtitle">'
            . $this->getPositionLabel($section->getAdminType(), $section->position) . ' · '
            . $this->getPositionLabel($asset->getAdminType(), $asset->position) . ' · '
            . $this->getPositionLabel($hotspot->getAdminType(), $hotspot->position) . '</h2>',
            $html,
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
        self::assertStringContainsString(
            '<h2 class="header-subtitle">'
            . $this->getPositionLabel($section->getAdminType(), $section->position) . ' · '
            . $this->getPositionLabel($sectionAsset->getAdminType(), $sectionAsset->position) . ' · '
            . $this->getPositionLabel($hotspot->getAdminType(), $hotspot->position) . ' · '
            . $this->getPositionLabel($asset->getAdminType(), $asset->position) . '</h2>',
            $html,
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

    private function getPositionLabel(string $type, int $position): string
    {
        return Yii::t('skeleton', 'COMMON_MODEL_ID', ['model' => $type, 'id' => $position]);
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
