<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Controllers;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\Traits\HotspotTrait;
use Hirtz\Cms\Models\Block;
use Hirtz\Cms\Models\Entry;
use Hirtz\Media\Modules\Admin\Controllers\Traits\AssetControllerTrait;
use Hirtz\Cms\Hotspot\Modules\Admin\Module;
use Hirtz\Skeleton\Web\Controller;
use Override;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * A hotspot asset is edited by whoever may edit the cms asset its hotspot sits on, which is what
 * {@see HotspotTrait::findHotspot()} checks.
 *
 * @extends Controller<Module>
 */
class HotspotAssetController extends Controller
{
    use AssetControllerTrait;
    use HotspotTrait;

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'verbs' => $this->getAssetVerbs(),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'create',
                            'delete',
                            'delete-all',
                            'index',
                            'order',
                            'remove',
                            'status',
                            'update',
                        ],
                        'roles' => [Block::AUTH_BLOCK, Entry::AUTH_ENTRY],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(?int $hotspot = null): Response|string
    {
        return $this->renderIndex($this->findHotspotModel($hotspot));
    }

    public function actionCreate(
        ?int $hotspot = null,
        ?int $file = null,
        ?int $folder = null,
        ?string $q = null,
        ?int $asset = null,
    ): Response|string {
        $model = $this->findHotspotModel($hotspot);

        return $this->createAsset($model, $file, $folder, $q, $asset ? $this->findHotspotAsset($asset) : null);
    }

    public function actionUpdate(int $id): Response|string
    {
        return $this->updateAsset($this->findHotspotAsset($id));
    }

    public function actionDelete(int $id): Response|string
    {
        return $this->deleteAsset($this->findHotspotAsset($id));
    }

    public function actionStatus(int $id): Response
    {
        return $this->updateStatus($this->findHotspotAsset($id));
    }

    public function actionRemove(
        ?int $hotspot = null,
        ?int $file = null,
        ?int $folder = null,
        ?string $q = null,
    ): Response|string {
        return $this->removeAsset($this->findHotspotModel($hotspot), $file, $folder, $q);
    }

    public function actionDeleteAll(?int $hotspot = null): Response
    {
        return $this->deleteAssets($this->findHotspotModel($hotspot));
    }

    public function actionOrder(?int $hotspot = null): string
    {
        return $this->reorderAssets($this->findHotspotModel($hotspot));
    }

    protected function findHotspotModel(?int $hotspot): Hotspot
    {
        if (!$hotspot) {
            throw new NotFoundHttpException();
        }

        /** @var Hotspot */
        return $this->findAssetModel($this->findHotspot($hotspot));
    }

    protected function findHotspotAsset(int $id): HotspotAsset
    {
        /** @var HotspotAsset $asset */
        $asset = $this->findAsset($id, HotspotAsset::class);

        // Throws when the cms asset the hotspot sits on may not be edited.
        $this->findHotspot($asset->model_id);

        return $asset;
    }
}
