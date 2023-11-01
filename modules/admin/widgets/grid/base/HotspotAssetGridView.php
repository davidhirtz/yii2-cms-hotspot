<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grid\base;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\modules\admin\widgets\grids\AssetGridView;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\models\AssetInterface;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;

/**
 * The HotspotAssetGridView widget displays {@link HotspotAsset} models in a grid view.
 */
class HotspotAssetGridView extends AssetGridView
{
    /**
     * @return void
     */
    public function init(): void
    {
        parent::init();
        $this->orderRoute = ['/admin/hotspot-asset/order', 'id' => $this->parent->id];
    }

    protected function getRowButtons(AssetInterface $asset): array
    {
        $user = Yii::$app->getUser();
        $buttons = [];

        if ($this->isSortedByPosition() && $this->dataProvider->getCount() > 1) {
            $buttons[] = $this->getSortableButton();
        }

        if ($user->can('fileUpdate', ['file' => $asset->file])) {
            $buttons[] = $this->getFileUpdateButton($asset);
        }

        $buttons[] = $this->getUpdateButton($asset);
        $buttons[] = $this->getDeleteButton($asset);

        return $buttons;
    }

    /**
     * @param HotspotAsset $model
     */
    protected function getRoute(ActiveRecordInterface $model, array $params = []): array|false
    {
        return array_merge($model->getAdminRoute(), $params);
    }

    protected function getIndexRoute(): array
    {
        return ['/admin/hotspot-asset/index', 'hotspot' => $this->parent->id];
    }

    protected function getCreateRoute(): array
    {
        return ['/admin/hotspot-asset/create', 'hotspot' => $this->parent->id];
    }

    protected function getDeleteRoute(ActiveRecordInterface $model, array $params = []): array|false
    {
        return array_merge(['/admin/hotspot-asset/delete', 'id' => $model->id], $params);
    }


    /**
     * @return ActiveQuery
     */
    protected function getParentAssetQuery()
    {
        return $this->parent->getAssets()
            ->with(['file', 'file.folder'])
            ->limit($this->maxAssetCount);
    }
}