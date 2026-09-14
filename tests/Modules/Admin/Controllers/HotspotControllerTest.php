<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Modules\Admin\Controllers;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Entry;
use Hirtz\Media\Models\Asset;
use Hirtz\Media\Modules\Admin\Widgets\Grids\AssetGridView;
use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Override;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * A hotspot is edited by whoever may edit the cms asset it sits on, so the whole controller answers to the entry
 * permission.
 */
class HotspotControllerTest extends TestCase
{
    use HotspotFixtureTrait;

    /**
     * Declared rather than merged: both fixture traits carry a `fixtures()` of their own.
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            ...$this->cmsFixtures(),
            'asset' => [
                'class' => \Hirtz\Cms\Test\Fixtures\AssetFixture::class,
                'dataFile' => '@hotspot/Test/Fixtures/Data/asset.php',
            ],
            'hotspot' => \Hirtz\Cms\Hotspot\Test\Fixtures\HotspotFixture::class,
            'user' => UserFixture::class,
        ];
    }

    public function testTheUpdatePageRendersTheForm(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString('name="Hotspot[name]"', $html);
        self::assertStringContainsString('Test Hotspot 1', $html);
    }

    /**
     * The hotspot has no page of its own to lead back to, and its assets live on their own page since the update
     * page stopped embedding their grid.
     */
    public function testTheUpdatePageLinksToTheAssetAndToItsOwnAssets(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);
        $hotspot = Hotspot::findOne(1);

        self::assertIsString($html);
        self::assertStringContainsString(Url::toRoute($hotspot->asset->getAdminRoute()), $html);
        self::assertStringContainsString(Url::toRoute(HotspotAsset::getAdminIndexRoute($hotspot)), $html);
        self::assertStringNotContainsString(AssetGridView::ID, $html);
    }

    public function testTheUpdatePageIsForbiddenWithoutTheEntryPermission(): void
    {
        Yii::$app->getUser()->setIdentity($this->getUserFromFixture('admin'));

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 1]);
    }

    public function testAnUnknownHotspotIsNotFound(): void
    {
        $this->login();

        $this->expectException(NotFoundHttpException::class);
        Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => 99999]);
    }

    public function testTheHotspotIsSaved(): void
    {
        $this->login();

        $response = $this->post('admin/hotspot/hotspot/update', ['id' => 1], [
            'Hotspot' => [
                'status' => Hotspot::STATUS_ENABLED,
                'name' => 'Renamed',
                'x' => '30',
                'y' => '40',
            ],
        ]);

        self::assertInstanceOf(Response::class, $response);

        $hotspot = Hotspot::findOne(1);

        self::assertSame('Renamed', $hotspot->name);
        self::assertSame(30.0, (float)$hotspot->x);
    }

    /**
     * Dragging a hotspot posts through `fetch()` rather than htmx, so the action answers with the flashes alone and
     * the page it was dragged on stays where it is.
     */
    public function testTheDraggedHotspotIsAnsweredWithItsFlash(): void
    {
        $this->login();

        Yii::$app->getRequest()->getHeaders()->set('X-Requested-With', 'XMLHttpRequest');

        $html = $this->post('admin/hotspot/hotspot/update', ['id' => 1], [
            'Hotspot' => [
                'x' => '30',
                'y' => '40',
                'position' => '2',
            ],
        ]);

        self::assertIsString($html);
        self::assertStringContainsString('hx-swap-oob="beforeend:#flashes"', $html);
        self::assertStringContainsString(Yii::t('hotspot', 'HOTSPOT_SUCCESS_UPDATED'), $html);
        self::assertSame(30.0, (float)Hotspot::findOne(1)->x);
    }

    public function testAHotspotIsCreatedOnTheAsset(): void
    {
        $this->login();

        $response = $this->post('admin/hotspot/hotspot/create', ['id' => 4], [
            'Hotspot' => [
                'status' => Hotspot::STATUS_ENABLED,
                'name' => 'A new hotspot',
                'x' => '10',
                'y' => '90',
            ],
        ]);

        self::assertInstanceOf(Response::class, $response);
        self::assertNotNull($this->findHotspotByName('A new hotspot'));
    }

    /**
     * The coordinates are a percentage of the image, so a hotspot outside it is refused — and the error reported is
     * the hotspot's own, not the asset's.
     */
    public function testAHotspotOutsideTheImageIsRefused(): void
    {
        $this->login();

        try {
            $this->post('admin/hotspot/hotspot/create', ['id' => 4], [
                'Hotspot' => [
                    'status' => Hotspot::STATUS_ENABLED,
                    'name' => 'Out of bounds',
                    'x' => '150',
                    'y' => '10',
                ],
            ]);

            self::fail('The hotspot was accepted.');
        } catch (\yii\web\BadRequestHttpException $exception) {
            self::assertNotSame('', $exception->getMessage());
        }

        self::assertNull($this->findHotspotByName('Out of bounds'));
    }

    /**
     * Only a cms asset carries hotspots, so one belonging to anything else is not found.
     */
    public function testAHotspotCannotBeCreatedOnANonCmsAsset(): void
    {
        $this->login();

        $asset = Asset::findOne(8);
        self::assertNotNull($asset);

        $this->expectException(NotFoundHttpException::class);
        $this->post('admin/hotspot/hotspot/create', ['id' => $asset->id]);
    }

    public function testCreateRefusesAGetRequest(): void
    {
        $this->login();

        $this->expectException(MethodNotAllowedHttpException::class);
        Yii::$app->runAction('admin/hotspot/hotspot/create', ['id' => 4]);
    }

    public function testAHotspotIsDuplicatedWithItsAssets(): void
    {
        $this->login();

        $response = $this->post('admin/hotspot/hotspot/duplicate', ['id' => 1]);

        self::assertInstanceOf(Response::class, $response);

        $duplicate = $this->findHotspotByName('Test Hotspot 1', 1);

        self::assertNotNull($duplicate);
        self::assertSame(1, $duplicate->asset_count);
    }

    /**
     * The name is a custom attribute, so it cannot be a query condition.
     */
    protected function findHotspotByName(string $name, ?int $exceptId = null): ?Hotspot
    {
        foreach (Hotspot::find()->all() as $hotspot) {
            if ($hotspot->name === $name && $hotspot->id !== $exceptId) {
                return $hotspot;
            }
        }

        return null;
    }

    public function testAHotspotIsDeleted(): void
    {
        $this->login();

        $response = $this->post('admin/hotspot/hotspot/delete', ['id' => 2]);

        self::assertInstanceOf(Response::class, $response);
        self::assertNull(Hotspot::findOne(2));
    }

    public function testTheAssetIndexOfAHotspotIsRendered(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/hotspot/hotspot-asset/index', ['hotspot' => 1]);

        self::assertIsString($html);
        self::assertStringContainsString(AssetGridView::ID, $html);
    }

    public function testTheAssetIndexNeedsAHotspot(): void
    {
        $this->login();

        $this->expectException(NotFoundHttpException::class);
        Yii::$app->runAction('admin/hotspot/hotspot-asset/index');
    }

    private function post(string $route, array $params = [], array $bodyParams = []): mixed
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = Yii::$app->getRequest();
        $request->setBodyParams([...$bodyParams, $request->csrfParam => $request->getCsrfToken()]);

        return Yii::$app->runAction($route, $params);
    }

    private function login(): User
    {
        $user = $this->getUserFromFixture('admin');

        $auth = Yii::$app->getAuthManager();
        $auth->assign($auth->getPermission(Entry::AUTH_ENTRY), $user->id);

        Yii::$app->getUser()->setIdentity($user);

        return $user;
    }

    private function getUserFromFixture(string $key): User
    {
        /** @var UserFixture $fixture */
        $fixture = $this->getFixture('user');

        return User::findOne($fixture->data[$key]['id']);
    }
}
