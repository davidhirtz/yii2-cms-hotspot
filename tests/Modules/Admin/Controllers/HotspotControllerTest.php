<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Tests\Modules\Admin\Controllers;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Test\TestCase;
use Hirtz\Cms\Hotspot\Test\Traits\HotspotFixtureTrait;
use Hirtz\Cms\Models\Block;
use Hirtz\Cms\Models\BlockAsset;
use Hirtz\Cms\Models\Entry;
use Hirtz\Media\Models\Asset;
use Hirtz\Media\Modules\Admin\Widgets\Grids\AssetGridView;
use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Override;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * A hotspot is edited by whoever may edit the cms asset it sits on, so the controller answers to the permission of
 * that asset: the entry one for an entry or section asset, the block one for a block asset.
 */
class HotspotControllerTest extends TestCase
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
        self::assertStringContainsString(Url::toRoute($hotspot->asset->getAdminRoute() ?: []), $html);
        self::assertStringContainsString(Url::toRoute(HotspotAsset::getAdminIndexRoute($hotspot)), $html);
        self::assertStringNotContainsString(AssetGridView::ID, $html);
    }

    public function testTheUpdatePageIsForbiddenWithoutTheEntryPermission(): void
    {
        $this->getWebUser()->setIdentity($this->getUserFromFixture('admin'));

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

        $this->getWebRequest()->getHeaders()->set('X-Requested-With', 'XMLHttpRequest');

        $html = $this->post('admin/hotspot/hotspot/update', ['id' => 1], [
            'Hotspot' => [
                'x' => '30',
                'y' => '40',
                'position' => '2',
            ],
        ]);

        self::assertIsString($html);
        self::assertStringContainsString('id="flashes"', $html);
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
     * Creating a hotspot posts through `fetch()` as well, so the flash the action sets travels in the response
     * beside the hotspot — and an unnamed one is titled after its position rather than after nothing.
     */
    public function testACreatedHotspotIsAnsweredWithItsFlashAndDisplayName(): void
    {
        $this->login();

        $response = $this->post('admin/hotspot/hotspot/create', ['id' => 4], [
            'Hotspot' => [
                'x' => '10',
                'y' => '90',
            ],
        ]);

        self::assertInstanceOf(Response::class, $response);

        /** @var array{hotspot: array{displayName: string}, flashes: string} $data */
        $data = Json::decode(Json::encode($response->data));

        $hotspot = Hotspot::find()->orderBy(['id' => SORT_DESC])->one();
        self::assertNotNull($hotspot);

        self::assertStringContainsString('id="flashes"', $data['flashes']);
        self::assertStringContainsString(Yii::t('hotspot', 'HOTSPOT_SUCCESS_CREATED'), $data['flashes']);
        self::assertSame($hotspot->getAdminName(), $data['hotspot']['displayName']);
        self::assertSame(3, $hotspot->position);
        self::assertStringContainsString('#3', $data['hotspot']['displayName']);
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

    /**
     * A block asset carries hotspots under the section flag and answers to the block permission (monorepo issue
     * #145).
     */
    public function testAHotspotIsCreatedOnABlockAsset(): void
    {
        $asset = $this->createBlockAsset();
        $this->login(Block::AUTH_BLOCK);

        $response = $this->post('admin/hotspot/hotspot/create', ['id' => $asset->id], [
            'Hotspot' => [
                'status' => Hotspot::STATUS_ENABLED,
                'name' => 'Block hotspot',
                'x' => '10',
                'y' => '20',
            ],
        ]);

        self::assertInstanceOf(Response::class, $response);

        $hotspot = $this->findHotspotByName('Block hotspot');
        self::assertNotNull($hotspot);
        self::assertSame($asset->id, $hotspot->asset_id);
        self::assertSame(1, BlockAsset::findOne($asset->id)?->getAttribute('hotspot_count'));
    }

    public function testTheUpdatePageOfABlockHotspotRendersTheBlockHeader(): void
    {
        $hotspot = $this->createBlockHotspot();
        $this->login(Block::AUTH_BLOCK);

        $html = Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => $hotspot->id]);

        self::assertIsString($html);
        self::assertStringContainsString('name="Hotspot[name]"', $html);
        self::assertStringContainsString("/admin/cms/block/update?id={$hotspot->asset->model_id}", $html);
        self::assertStringContainsString("/admin/hotspot/hotspot/delete?id=$hotspot->id", $html);
    }

    public function testABlockHotspotIsForbiddenWithTheEntryPermissionAlone(): void
    {
        $hotspot = $this->createBlockHotspot();
        $this->login();

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/hotspot/hotspot/update', ['id' => $hotspot->id]);
    }

    public function testAHotspotIsRefusedOnABlockAssetWhileSectionAssetHotspotsAreOff(): void
    {
        $asset = $this->createBlockAsset();
        $this->login(Block::AUTH_BLOCK);

        Hotspot::getModule()->enableSectionAssetHotspots = false;

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

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $bodyParams
     */
    private function post(string $route, array $params = [], array $bodyParams = []): mixed
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = $this->getWebRequest();
        $request->setBodyParams([...$bodyParams, $request->csrfParam => $request->getCsrfToken()]);

        return Yii::$app->runAction($route, $params);
    }

    private function createBlockAsset(): BlockAsset
    {
        $module = Block::getModule();
        $module->enableBlocks = true;
        $module->enableBlockAssets = true;

        $block = Block::create();
        $block->name = 'Hotspot Block';
        self::assertTrue($block->insert(), print_r($block->getErrors(), true));

        $asset = BlockAsset::create();
        $asset->populateModelRelation($block);
        $asset->file_id = $this->getFileFixtureData('file-1')['id'];
        self::assertTrue($asset->insert(), print_r($asset->getErrors(), true));

        return $asset;
    }

    private function createBlockHotspot(): Hotspot
    {
        $hotspot = Hotspot::create();
        $hotspot->populateAssetRelation($this->createBlockAsset());
        $hotspot->name = 'Block hotspot';
        $hotspot->x = 10;
        $hotspot->y = 20;
        self::assertTrue($hotspot->insert(), print_r($hotspot->getErrors(), true));

        return $hotspot;
    }

    private function login(string $permission = Entry::AUTH_ENTRY): User
    {
        $user = $this->getUserFromFixture('admin');

        $auth = Yii::$app->getAuthManager();
        $auth->assign($auth->getPermission($permission), $user->id);

        $this->getWebUser()->setIdentity($user);

        return $user;
    }

    private function getUserFromFixture(string $key): User
    {
        /** @var UserFixture $fixture */
        $fixture = $this->getFixture('user');

        return User::findOne($fixture->data[$key]['id']);
    }
}
