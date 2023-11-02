<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\controllers;

use davidhirtz\yii2\cms\modules\admin\controllers\traits\AssetTrait;
use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\traits\HotspotTrait;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class HotspotController extends Controller
{
    use AssetTrait;
    use HotspotTrait;
    use ModuleTrait;

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'delete', 'update'],
                        'roles' => ['entryAssetUpdate', 'sectionAssetUpdate'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    public function actionCreate(int $id): Response|string
    {
        $asset = $this->findAsset($id, 'assetUpdate');

        $hotspot = Hotspot::create();
        $hotspot->populateAssetRelation($asset);

        $request = Yii::$app->getRequest();

        if ($hotspot->load($request->post())) {
            if ($hotspot->insert()) {
                return $request->getIsAjax() ? $this->asJson($hotspot) : $this->redirect(['update', 'id' => $hotspot->id]);
            }

            $errors = $asset->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        return $this->redirect($asset->getAdminRoute());
    }

    public function actionUpdate(int $id): Response|string
    {
        $hotspot = $this->findHotspot($id);
        $request = Yii::$app->getRequest();

        if ($hotspot->load($request->post()) && $hotspot->update()) {
            if (!$request->getIsAjax()) {
                $this->success(Yii::t('hotspot', 'The hotspot was updated.'));
                return $this->redirect(['update', 'id' => $hotspot->id]);
            }
        }

        // Check for AJAX and POST request as the site refresh after a new upload should still hit the render.
        return $request->getIsAjax() && $request->post() ? $this->asJson($hotspot) : $this->render('update', [
            'hotspot' => $hotspot,
        ]);
    }

    public function actionDelete(int $id): Response|string
    {
        $hotspot = $this->findHotspot($id);

        if ($hotspot->delete()) {
            if (Yii::$app->getRequest()->getIsAjax()) {
                return '';
            }

            $this->success(Yii::t('hotspot', 'The hotspot was deleted.'));
        }

        if ($errors = $hotspot->getFirstErrors()) {
            $this->error($errors);
        }

        return $this->redirect(['/admin/asset/update', 'id' => $hotspot->asset_id]);
    }
}