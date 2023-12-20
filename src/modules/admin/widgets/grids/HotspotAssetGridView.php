<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\modules\admin\widgets\grids\AssetGridView;
use davidhirtz\yii2\media\models\interfaces\AssetInterface;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecordInterface;

/**
 * The HotspotAssetGridView widget displays {@see HotspotAsset} models in a grid view.
 */
class HotspotAssetGridView extends AssetGridView
{
    public function init(): void
    {
        parent::init();

        // Override the order route set by parent class
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

    protected function getRoute(ActiveRecordInterface $model, array $params = []): array|false
    {
        /** @var HotspotAsset $model */
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

    protected function getDeleteRoute(ActiveRecordInterface $model, array $params = []): array
    {
        return ['/admin/hotspot-asset/delete', 'id' => $model->getPrimaryKey(), ...$params];
    }

    protected function getParentAssetQuery(): ActiveQuery
    {
        return $this->parent->getAssets()
            ->with(['file', 'file.folder'])
            ->limit($this->maxAssetCount);
    }
}
