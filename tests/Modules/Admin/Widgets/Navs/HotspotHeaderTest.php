<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Modules\Admin\Widgets\Navs;

use Hirtz\Cms\Hotspot\Test\Fixtures\HotspotFixture;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Test\Fixtures\AssetFixture;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Override;
use Yii;

/**
 * The header renders the one of the asset the hotspot hangs on, so what the view adds to it has to survive that.
 */
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

    private function login(): void
    {
        $this->getWebUser()->setIdentity(User::findOne(1));
    }
}
