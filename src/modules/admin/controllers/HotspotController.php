<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Modules\Admin\Controllers;

use Hirtz\Cms\hotspot\models\actions\DuplicateHotspot;
use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\hotspot\Modules\Admin\Controllers\Traits\HotspotTrait;
use Hirtz\Cms\Modules\Admin\Traits\AssetTrait;
use Hirtz\Cms\modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
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

    #[\Override]
    public function behaviors(): array
    {
        return [...parent::behaviors(), 'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['create', 'delete', 'duplicate', 'update'],
                    'roles' => ['entryAssetUpdate', 'sectionAssetUpdate'],
                ],
            ],
        ], 'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['post'],
                'delete' => ['post'],
                'duplicate' => ['post'],
            ],
        ]];
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

        // Prevent site reload on new AJAX upload.
        if ($request->getIsAjax() && $request->post()) {
            return $this->asJson($hotspot);
        }

        return $this->render('update', [
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

        $this->error($hotspot);

        return $this->redirect(['/admin/asset/update', 'id' => $hotspot->asset_id]);
    }

    public function actionDuplicate(int $id): Response|string
    {
        $hotspot = $this->findHotspot($id);
        $duplicate = DuplicateHotspot::create(['hotspot' => $hotspot]);

        if ($this->error($duplicate)) {
            return $this->redirect(['update', 'id' => $hotspot->id]);
        }

        $this->success(Yii::t('hotspot', 'The hotspot was duplicated.'));
        return $this->redirect(['update', 'id' => $duplicate->id]);
    }
}
