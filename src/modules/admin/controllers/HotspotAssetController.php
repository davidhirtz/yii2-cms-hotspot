<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\modules\admin\controllers;

use Hirtz\Cms\hotspot\models\actions\ReorderHotspotAssets;
use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Cms\hotspot\modules\admin\controllers\traits\HotspotTrait;
use Hirtz\Cms\modules\ModuleTrait;
use Hirtz\Media\models\File;
use Hirtz\Media\models\Folder;
use Hirtz\Media\modules\admin\controllers\traits\FileControllerTrait;
use Hirtz\Media\modules\admin\data\FileActiveDataProvider;
use Hirtz\Skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class HotspotAssetController extends Controller
{
    use HotspotTrait;
    use ModuleTrait;
    use FileControllerTrait;

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'delete', 'order', 'update'],
                        'roles' => ['entryAssetUpdate', 'sectionAssetUpdate'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['entryAssetCreate', 'sectionAssetCreate'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'order' => ['post'],
                ],
            ],
        ]);
    }

    public function actionIndex(int $hotspot, ?int $folder = null, ?string $q = null): Response|string
    {
        $hotspot = $this->findHotspot($hotspot);

        $provider = Yii::$container->get(FileActiveDataProvider::class, [], [
            'folder' => Folder::findOne($folder),
            'search' => $q,
        ]);

        return $this->render('index', [
            'provider' => $provider,
            'hotspot' => $hotspot,
        ]);
    }

    public function actionCreate(int $hotspot, ?int $file = null, ?int $folder = null): Response|string
    {
        $hotspot = $this->findHotspot($hotspot);

        if (!($file = File::findOne($file) ?: $this->insertFileFromRequest($folder))) {
            return '';
        }

        $asset = HotspotAsset::create();
        $asset->populateHotspotRelation($hotspot);
        $asset->populateFileRelation($file);

        if (!$asset->insert()) {
            $errors = $asset->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            return '';
        }

        $this->success(Yii::t('hotspot', 'The hotspot asset was added.'));
        return $this->redirect(['hotspot/update', 'id' => $hotspot->id]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $asset = $this->findAsset($id);

        if ($asset->load(Yii::$app->getRequest()->post())) {
            if ($asset->update()) {
                $this->success(Yii::t('hotspot', 'The hotspot asset was updated.'));
            }

            if (!$asset->hasErrors()) {
                return $this->redirect(['hotspot/update', 'id' => $asset->hotspot_id]);
            }
        }

        return $this->render('update', [
            'asset' => $asset,
        ]);
    }

    public function actionDelete(int $id): Response|string
    {
        $asset = $this->findAsset($id);

        if ($asset->delete()) {
            if (Yii::$app->getRequest()->getIsAjax()) {
                return '';
            }

            $this->success(Yii::t('hotspot', 'The hotspot asset was deleted.'));
            return $this->redirect(['hotspot/update', 'id' => $asset->hotspot_id]);
        }

        $errors = $asset->getFirstErrors();
        throw new BadRequestHttpException(reset($errors));
    }

    public function actionOrder(int $id): void
    {
        ReorderHotspotAssets::runWithBodyParam('hotspot-asset', [
            'hotspot' => $this->findHotspot($id),
        ]);
    }

    private function findAsset(int $id): HotspotAsset
    {
        if (!$asset = HotspotAsset::findOne($id)) {
            throw new NotFoundHttpException();
        }

        $hotspot = $this->findHotspot($asset->hotspot_id);
        $asset->populateHotspotRelation($hotspot);

        return $asset;
    }
}
