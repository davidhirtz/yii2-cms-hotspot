<?php

namespace davidhirtz\yii2\hotspot\modules\admin\widgets\grid\base;

use davidhirtz\yii2\hotspot\models\Hotspot;
use davidhirtz\yii2\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grid\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\Timeago;
use yii\data\ArrayDataProvider;
use Yii;

/**
 * Class HotspotAssetParentGridView
 * @package davidhirtz\yii2\hotspot\modules\admin\widgets\grid\base
 */
class HotspotAssetParentGridView extends GridView
{
    /**
     * @var File
     */
    public $file;

    /**
     * @var bool
     */
    public $showHeader = false;

    /**
     * @var string
     */
    public $layout = '{items}';

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!$this->dataProvider) {
            $this->dataProvider = new ArrayDataProvider([
                'allModels' => HotspotAsset::find()
                    ->where(['file_id' => $this->file->id])
                    ->with(['hotspot'])
                    ->orderBy(['updated_at' => SORT_DESC])
                    ->all()
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
            $asset->populateRelation('file', $this->file);
        }

        parent::init();
    }

    /**
     * @return array
     */
    public function statusColumn()
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

    /**
     * @return array
     */
    public function typeColumn()
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

    /**
     * @return array
     */
    public function nameColumn()
    {
        return [
            'content' => function (HotspotAsset $asset) {
                return Html::tag('strong', Html::a($asset->hotspot->getDisplayName(), $this->getRoute($asset)));
            }
        ];
    }

    /**
     * @return array
     */
    public function assetCountColumn()
    {
        return [
            'headerOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'contentOptions' => ['class' => 'd-none d-md-table-cell text-center'],
            'content' => function (HotspotAsset $asset) {
                return Html::a(Yii::$app->getFormatter()->asInteger($asset->hotspot->asset_count), $this->getRoute($asset), ['class' => 'badge']);
            }
        ];
    }

    /**
     * @return array
     */
    public function updatedAtColumn()
    {
        return [
            'headerOptions' => ['class' => 'd-none d-lg-table-cell'],
            'contentOptions' => ['class' => 'd-none d-lg-table-cell text-nowrap'],
            'content' => function (HotspotAsset $asset) {
                return Timeago::tag($asset->updated_at);
            }
        ];
    }

    /**
     * @return array
     */
    public function buttonsColumn()
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (HotspotAsset $asset) {
                $user = Yii::$app->getUser();
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
     * @param array $params
     * @return array|false
     */
    protected function getRoute($model, $params = [])
    {
        return array_merge(['hotspot/update', 'id' => $model->hotspot_id], $params);
    }

    /**
     * @return HotspotAsset
     */
    public function getModel()
    {
        return HotspotAsset::instance();
    }
}