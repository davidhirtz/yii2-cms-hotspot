<?php

namespace davidhirtz\yii2\hotspot\modules\admin\controllers;

use davidhirtz\yii2\hotspot\models\HotspotAsset;
use davidhirtz\yii2\hotspot\models\Entry;
use davidhirtz\yii2\hotspot\modules\admin\controllers\traits\HotspotAssetTrait;
use davidhirtz\yii2\hotspot\modules\admin\controllers\traits\HotspotTrait;
use davidhirtz\yii2\hotspot\modules\admin\controllers\traits\EntryTrait;
use davidhirtz\yii2\hotspot\modules\admin\controllers\traits\SectionTrait;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\hotspot\models\Asset;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\modules\admin\controllers\traits\FileTrait;
use davidhirtz\yii2\media\modules\admin\data\FileActiveDataProvider;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
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
                        'actions' => ['create', 'delete', 'update'],
                        'roles' => ['hotspotUpdate'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @param int $hotspot
     * @param int|null $file
     * @param int|null $folder
     * @return string|Response
     */
    public function actionCreate($hotspot, $file = null, $folder = null)
    {
        $hotspot = $this->findHotspot($hotspot, 'hotspotUpdate');

        $request = Yii::$app->getRequest();
        $user = Yii::$app->getUser();

        if(!($file = File::findOne($file) ?: $this->insertFileFromRequest($folder))) {
            return '';
        }

        $asset = new HotspotAsset();
        $asset->pop = $entry;
        $asset->section_id = $section;
        $asset->populateFileRelation($file);

        if (!$user->can($asset->isEntryAsset() ? 'entryAssetCreate' : 'sectionAssetCreate', ['asset' => $asset])) {
            throw new ForbiddenHttpException();
        }

        if (!$asset->insert()) {
            $errors = $asset->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        if ($request->getIsAjax()) {
            return '';
        }

        $this->success(Yii::t('hotspot', 'The asset was added.'));
        return $this->redirectToParent($asset);
//
//
//        $asset->file_id = $file->id;
//
//
//        if (!$asset->insert()) {
//            $errors = $asset->getFirstErrors();
//            throw new BadRequestHttpException(reset($errors));
//        }
//
//        if ($request->getIsAjax()) {
//            return '';
//        }
//
//        $this->success($isNew ? Yii::t('hotspot', 'The asset was created.') : Yii::t('hotspot', 'The asset was added.'));
        return $this->redirect(['/admin/hotspot/update', 'id' => $asset->hotspot_id]);
    }

    /**
     * @param int $id
     * @return string|Response
     */
    public function actionUpdate(int $id)
    {
        $asset = $this->findAsset($id);

        if ($asset->load(Yii::$app->getRequest()->post())) {
            if ($asset->update()) {
                $this->success(Yii::t('hotspot', 'The asset was updated.'));
            }

            if (!$asset->hasErrors()) {
                return $this->redirect(['/admin/hotspot/update', 'id' => $asset->hotspot_id]);
            }
        }

        /** @noinspection MissedViewInspection */
        return $this->render('update', [
            'asset' => $asset,
        ]);
    }

    /**
     * @param int $id
     * @return string|Response
     */
    public function actionDelete($id)
    {
        $asset = $this->findAsset($id);

        if ($asset->delete()) {
            if (Yii::$app->getRequest()->getIsAjax()) {
                return '';
            }

            $this->success(Yii::t('hotspot', 'The asset was deleted.'));
            return $this->redirect(['/admin/hotspot/update', 'id' => $asset->hotspot_id]);
        }

        $errors = $asset->getFirstErrors();
        throw new BadRequestHttpException(reset($errors));
    }

    /**
     * @param int $id
     * @param string|null $permissionName
     * @return HotspotAsset
     */
    protected function findAsset($id, $permissionName = null)
    {
        if (!$asset = HotspotAsset::findOne((int)$id)) {
            throw new NotFoundHttpException();
        }

        if (!Yii::$app->getUser()->can('hotspotUpdate', ['hotspot' => $asset->hotspot])) {
            throw new ForbiddenHttpException();
        }

        return $asset;
    }
}