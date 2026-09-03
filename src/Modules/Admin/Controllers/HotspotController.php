<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Controllers;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Cms\Hotspot\Models\Actions\DuplicateHotspot;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\Traits\HotspotTrait;
use Hirtz\Cms\Modules\Admin\Controllers\Traits\AssetControllerTrait;
use Hirtz\Cms\Modules\ModuleTrait;
use Hirtz\Skeleton\Web\Controller;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class HotspotController extends Controller
{
    use AssetControllerTrait;
    use HotspotTrait;
    use ModuleTrait;

    #[Override]
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

        if ($hotspot->load($this->request->post())) {
            if ($hotspot->insert()) {
                return $this->asJson($hotspot);
            }

            $errors = $asset->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        return $this->redirect($asset->getAdminRoute());
    }

    public function actionUpdate(int $id): Response|string
    {
        $hotspot = $this->findHotspot($id);

        if ($hotspot->load($this->request->post()) && $hotspot->update()) {
            if ($this->request->getIsAjax()) {
                return $this->asJson([]);
            }

            $this->success(Lang::t('hotspot', 'HOTSPOT_SUCCESS_UPDATED'));
            return $this->redirect(['update', 'id' => $hotspot->id]);
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

            $this->success(Lang::t('hotspot', 'HOTSPOT_SUCCESS_DELETED'));
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

        $this->success(Lang::t('hotspot', 'HOTSPOT_SUCCESS_DUPLICATED'));
        return $this->redirect(['update', 'id' => $duplicate->id]);
    }
}
