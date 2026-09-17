<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Controllers;

use Hirtz\Cms\Hotspot\Models\Actions\DuplicateHotspot;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Controllers\Traits\HotspotTrait;
use Hirtz\Cms\Hotspot\Modules\Admin\Module;
use Hirtz\Cms\Hotspot\Modules\ModuleTrait;
use Hirtz\Cms\Models\Block;
use Hirtz\Cms\Models\Entry;
use Hirtz\Media\Models\Asset;
use Hirtz\Skeleton\Web\Controller;
use Hirtz\Skeleton\Widgets\Flashes;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @extends Controller<Module>
 */
class HotspotController extends Controller
{
    use HotspotTrait;
    use ModuleTrait;

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'delete', 'duplicate', 'update'],
                        'roles' => [Block::AUTH_BLOCK, Entry::AUTH_ENTRY],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'delete' => ['post'],
                    'duplicate' => ['post'],
                ],
            ],
        ];
    }

    public function actionCreate(int $id, ?int $type = null): Response|string
    {
        $asset = $this->findAsset($id);

        $hotspot = Hotspot::instantiateFromPost($this->request->post(), $type);
        $hotspot->loadDefaultValues();

        $hotspot->populateAssetRelation($asset);

        if ($hotspot->load($this->request->post()) && !$this->request->isFormReload()) {
            if ($hotspot->insert()) {
                $this->success(Yii::t('hotspot', 'HOTSPOT_SUCCESS_CREATED'));

                // Creating a hotspot posts through `fetch()`, so the flashes travel with it rather than being
                // swapped in by htmx.
                return $this->asJson([
                    'hotspot' => $hotspot,
                    'flashes' => (string)Flashes::make(),
                ]);
            }

            $errors = $hotspot->getFirstErrors();
            throw new BadRequestHttpException(reset($errors) ?: null);
        }

        return $this->redirectToAsset($asset);
    }

    public function actionUpdate(int $id): Response|string
    {
        $hotspot = $this->findHotspot($id);

        if ($hotspot->load($this->request->post()) && !$this->request->isFormReload() && $hotspot->update()) {
            $this->success(Yii::t('hotspot', 'HOTSPOT_SUCCESS_UPDATED'));

            // Dragging a hotspot posts through `fetch()` and swaps the flashes in, so the page itself stays put.
            if ($this->request->getIsAjax()) {
                return (string)Flashes::make();
            }

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
            if ($this->request->getIsAjax()) {
                return '';
            }

            $this->success(Yii::t('hotspot', 'HOTSPOT_SUCCESS_DELETED'));
        }

        $this->error($hotspot);

        return $this->redirectToAsset($hotspot->asset);
    }

    protected function redirectToAsset(Asset $asset): Response
    {
        return $this->redirect($asset->getAdminRoute() ?: $asset::getAdminIndexRoute($asset->model));
    }

    /**
     * The gate is on adding: an asset the admin offers no hotspots for is not found here, while a hotspot that
     * already exists stays reachable through {@see HotspotTrait::findHotspot()}.
     */
    protected function findAsset(int $id): Asset
    {
        $asset = Asset::findOne($id);

        if (!$asset || !static::getModule()->allowsHotspots($asset)) {
            throw new NotFoundHttpException();
        }

        if (!$this->webuser->can($asset->getPermissionName())) {
            throw new ForbiddenHttpException();
        }

        return $asset;
    }

    public function actionDuplicate(int $id): Response|string
    {
        $hotspot = $this->findHotspot($id);
        $duplicate = DuplicateHotspot::create(['hotspot' => $hotspot]);

        if ($errors = $duplicate->getFirstErrors()) {
            $this->error($errors);
            return $this->redirect(['update', 'id' => $hotspot->id]);
        }

        $this->success(Yii::t('hotspot', 'HOTSPOT_SUCCESS_DUPLICATED'));
        return $this->redirect(['update', 'id' => $duplicate->id]);
    }
}
