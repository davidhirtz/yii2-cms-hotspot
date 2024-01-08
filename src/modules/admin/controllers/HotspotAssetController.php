<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\controllers;

use davidhirtz\yii2\cms\hotspot\models\actions\ReorderHotspotAssets;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\traits\HotspotTrait;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\models\Folder;
use davidhirtz\yii2\media\modules\admin\controllers\traits\FileTrait;
use davidhirtz\yii2\media\modules\admin\data\FileActiveDataProvider;
use davidhirtz\yii2\skeleton\web\Controller;
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
    use FileTrait;

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

    public function actionIndex(int $hotspot, ?int $folder = null, ?int $type = null, ?string $q = null): Response|string
    {
        $hotspot = $this->findHotspot($hotspot);

        $provider = Yii::$container->get(FileActiveDataProvider::class, [], [
            'folder' => Folder::findOne($folder),
            'type' => $type,
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
