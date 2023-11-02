<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\columns\CounterColumn;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\Timeago;
use yii\data\ActiveDataProvider;
use Yii;

/**
 * Displays {@see HotspotAsset} models in a grid related to {@link Asset}.
 */
class HotspotAssetParentGridView extends GridView
{
    public ?File $file = null;

    public $showHeader = false;
    public $layout = '{items}{pager}';

    public function init(): void
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new ActiveDataProvider([
                'query' => HotspotAsset::find()
                    ->where(['file_id' => $this->file->id])
                    ->with(['hotspot'])
                    ->orderBy(['updated_at' => SORT_DESC]),
            ]);
        }

        if (!$this->columns) {
            $this->columns = [
                $this->statusColumn(),
                $this->typeColumn(),
                $this->nameColumn(),
                $this->assetCountColumn(),
                $this->updatedAtColumn(),
                $this->buttonsColumn(),
            ];
        }

        /** @var HotspotAsset $asset */
        foreach ($this->dataProvider->getModels() as $asset) {
            $asset->populateFileRelation($this->file);
        }

        parent::init();
    }

    public function statusColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-center'],
            'content' => function (HotspotAsset $asset) {
                return Icon::tag($asset->hotspot->getStatusIcon(), [
                    'data-toggle' => 'tooltip',
                    'title' => $asset->hotspot->getStatusName(),
                ]);
            }
        ];
    }

    public function typeColumn(): array
    {
        return [
            'attribute' => 'type',
            'contentOptions' => ['class' => 'text-nowrap'],
            'visible' => count(Hotspot::getTypes()) > 1,
            'content' => function (HotspotAsset $asset) {
                return ($route = $this->getRoute($asset)) ? Html::a($asset->hotspot->getTypeName(), $route) : $asset->hotspot->getTypeName();
            }
        ];
    }

    public function nameColumn(): array
    {
        return [
            'content' => function (HotspotAsset $asset) {
                return Html::tag('strong', Html::a($asset->hotspot->getDisplayName(), $this->getRoute($asset)));
            }
        ];
    }

    public function assetCountColumn(): array
    {
        return [
            'attribute' => 'hotspot.asset_count',
            'class' => CounterColumn::class,
        ];
    }

    public function updatedAtColumn(): array
    {
        return [
            'attribute' => 'updated_at',
            'class' => Timeago::class,
        ];
    }

    public function buttonsColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (HotspotAsset $asset) {
                $buttons = [];

                $buttons[] = Html::a(Icon::tag('wrench'), $asset->getAdminRoute(), [
                    'class' => 'btn btn-primary',
                    'data-toggle' => 'tooltip',
                    'title' => Yii::t('hotspot', 'Edit Hotspot Asset'),
                ]);

                $buttons[] = Html::a(Icon::tag('trash'), ['admin/hotspot-asset/delete', 'id' => $asset->id], [
                    'class' => 'btn btn-danger btn-delete-asset d-none d-md-inline-block',
                    'data-confirm' => Yii::t('cms', 'Are you sure you want to remove this asset?'),
                    'data-ajax' => 'remove',
                    'data-target' => '#' . $this->getRowId($asset),
                ]);

                return Html::buttons($buttons);
            }
        ];
    }

    /**
     * @param HotspotAsset $model
     */
    protected function getRoute($model, $params = []): false|array
    {
        return ['hotspot/update', 'id' => $model->hotspot_id, ...$params];
    }

    public function getModel(): HotspotAsset
    {
        return HotspotAsset::instance();
    }
}