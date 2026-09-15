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
 * A hotspot is always reached from a cms asset, so both of its pages keep the entries item active.
 */
class CmsNavItemTest extends TestCase
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

    public function testTheHotspotPageActivatesTheEntriesItem(): void
    {
        $this->login();
        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString('<a class="nav-link active" href="/admin/cms/entry/index">', $html);
    }

    public function testTheHotspotAssetPageActivatesTheEntriesItem(): void
    {
        $this->login();
        $html = Yii::$app->runAction('admin/hotspot/hotspot-asset/index', ['hotspot' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString('<a class="nav-link active" href="/admin/cms/entry/index">', $html);
    }

    private function login(): void
    {
        Yii::$app->getUser()->setIdentity(User::findOne(1));
    }
}
