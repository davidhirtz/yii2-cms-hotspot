<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\columns\CounterColumn;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\timeago\TimeagoColumn;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * @extends GridView<HotspotAsset>
 */
class HotspotAssetParentGridView extends GridView
{
    public File $file;
    public string $language;

    public $showHeader = false;
    public $layout = '{items}{pager}';

    public function init(): void
    {
        Yii::$app->getI18n()->callback($this->language, function () {
            $this->dataProvider ??= new ActiveDataProvider([
                'query' => HotspotAsset::find()
                    ->where(['file_id' => $this->file->id])
                    ->with(['hotspot'])
                    ->orderBy(['updated_at' => SORT_DESC]),
            ]);

            $this->dataProvider->pagination->pageParam = "hotspot-asset-page-$this->language";

            /** @var HotspotAsset $asset */
            foreach ($this->dataProvider->getModels() as $asset) {
                $asset->populateFileRelation($this->file);
            }
        });

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

        parent::init();
    }

    public function statusColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-center'],
            'content' => fn (HotspotAsset $asset) => Icon::tag($asset->hotspot->getStatusIcon(), [
                'data-toggle' => 'tooltip',
                'title' => $asset->hotspot->getStatusName(),
            ])
        ];
    }

    public function typeColumn(): array
    {
        return [
            'attribute' => 'type',
            'contentOptions' => ['class' => 'text-nowrap'],
            'visible' => count($this->getModel()::getTypes()) > 1,
            'content' => fn (HotspotAsset $asset) => ($route = $this->getRoute($asset)) ? Html::a($asset->hotspot->getTypeName(), $route) : $asset->hotspot->getTypeName()
        ];
    }

    public function nameColumn(): array
    {
        return [
            'content' => fn (HotspotAsset $asset) => Html::tag('strong', Html::a($asset->hotspot->getDisplayName(), $this->getRoute($asset)))
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
            'class' => TimeagoColumn::class,
        ];
    }

    public function buttonsColumn(): array
    {
        return [
            'contentOptions' => ['class' => 'text-right text-nowrap'],
            'content' => function (HotspotAsset $asset): string {
                $buttons = [];

                $buttons[] = Html::a((string)Icon::tag('wrench'), $this->getI18nRoute($asset->getAdminRoute()), [
                    'class' => 'btn btn-primary',
                    'data-toggle' => 'tooltip',
                    'title' => Yii::t('hotspot', 'Edit Hotspot Asset'),
                ]);

                $buttons[] = Html::a((string)Icon::tag('trash'), $this->getI18nRoute(['/admin/hotspot-asset/delete', 'id' => $asset->id]), [
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
        return $this->getI18nRoute([
            '/admin/hotspot/update',
            'id' => $model->hotspot_id,
            ...$params,
        ]);
    }

    protected function getI18nRoute(array $route): array
    {
        return [
            ...$route,
            'language' => $this->language !== Yii::$app->language ? $this->language : null,
        ];
    }

    public function getModel(): HotspotAsset
    {
        return HotspotAsset::instance();
    }
}
