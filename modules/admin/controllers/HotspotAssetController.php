<?php

namespace davidhirtz\yii2\hotspot\modules\admin\controllers;

use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\hotspot\models\HotspotAsset;
use davidhirtz\yii2\hotspot\modules\admin\controllers\traits\HotspotTrait;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\modules\admin\controllers\traits\FileTrait;
use davidhirtz\yii2\media\modules\admin\data\FileActiveDataProvider;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class HotspotAssetController
 * @package davidhirtz\yii2\hotspot\modules\admin\controllers
 */
class HotspotAssetController extends Controller
{
    use HotspotTrait;
    use ModuleTrait;
    use FileTrait;

    /**
     * @inheritDoc
     */
    public function behaviors()
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

    /**
     * @param int $hotspot the hotspot id – needs to be named `hotspot` because of `FileGrid` autogenerated links
     * @param int|null $folder
     * @param int|null $type
     * @param string|null $q
     * @return string
     */
    public function actionIndex($hotspot, $folder = null, $type = null, $q = null)
    {
        $hotspot = $this->findHotspot($hotspot);

        /** @var FileActiveDataProvider $provider */
        $provider = Yii::createObject([
            'class' => 'davidhirtz\yii2\media\modules\admin\data\FileActiveDataProvider',
            'folderId' => $folder,
            'type' => $type,
            'search' => $q,
        ]);

        return $this->render('index', [
            'provider' => $provider,
            'hotspot' => $hotspot,
        ]);
    }

    /**
     * @param int $hotspot the hotspot id – needs to be named `hotspot` because of `FileGrid` autogenerated links
     * @param int|null $file
     * @param int|null $folder
     * @return string|Response
     */
    public function actionCreate($hotspot, $file = null, $folder = null)
    {
        $hotspot = $this->findHotspot($hotspot);

        if (!($file = File::findOne($file) ?: $this->insertFileFromRequest($folder))) {
            return '';
        }

        $asset = new HotspotAsset();
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

    /**
     * @param int $id the hotspot asset id
     * @return string|Response
     */
    public function actionUpdate($id)
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

    /**
     * @param int $id the hotspot asset id
     * @return string|Response
     */
    public function actionDelete($id)
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

    /**
     * @param int $id the hotspot id
     */
    public function actionOrder($id)
    {
        $hotspot = $this->findHotspot($id);
        $assetIds = array_map('intval', array_filter(Yii::$app->getRequest()->post('asset', [])));

        if ($assetIds) {
            $hotspot->updateAssetOrder($assetIds);
        }
    }

    /**
     * @param int $id
     * @return HotspotAsset
     */
    private function findAsset($id)
    {
        if (!$asset = HotspotAsset::findOne((int)$id)) {
            throw new NotFoundHttpException();
        }

        // Use `findHotspot` here for permission check
        $asset->populateHotspotRelation($this->findHotspot($asset->hotspot_id));
        return $asset;
    }
}