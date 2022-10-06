<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grid\base;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\modules\admin\widgets\grid\base\AssetGridView;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use Yii;
use yii\db\ActiveQuery;

/**
 * The HotspotAssetGridView widget displays {@link HotspotAsset} models in a grid view.
 */
class HotspotAssetGridView extends AssetGridView
{
    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->orderRoute = ['/admin/hotspot-asset/order', 'id' => $this->parent->id];
    }

    /**
     * @param Asset $asset
     * @return array
     */
    protected function getRowButtons($asset)
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
     * @param array $params
     * @return array|false
     */
    protected function getRoute($model, $params = [])
    {
        return array_merge($model->getAdminRoute(), $params);
    }

    /**
     * @return array
     */
    protected function getIndexRoute()
    {
        return ['/admin/hotspot-asset/index', 'hotspot' => $this->parent->id];
    }

    /**
     * @return array
     */
    protected function getCreateRoute()
    {
        return ['/admin/hotspot-asset/create', 'hotspot' => $this->parent->id];
    }

    /**
     * @param Asset $model
     * @param array $params
     * @return array|false
     */
    protected function getDeleteRoute($model, $params = [])
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