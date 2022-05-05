<?php

namespace davidhirtz\yii2\cms\modules\admin\widgets\grid\columns;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\modules\admin\widgets\grid\AssetGridView;
use davidhirtz\yii2\skeleton\helpers\Html;
use Yii;
use yii\grid\Column;

/**
 * Class AssetThumbnailColumn
 * @package davidhirtz\yii2\cms\modules\admin\widgets\grid\columns
 * @noinspection PhpMultipleClassDeclarationsInspection
 *
 * @property AssetGridView $grid
 */
class AssetThumbnailColumn extends Column
{
    /**
     * @var string[]
     */
    public $headerOptions = [
        'style' => 'width:150px',
    ];

    /**
     * @param Asset $model
     * @param string $key
     * @param $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if (!$model->file->hasPreview()) {
            return '';
        }

        $content = Html::tag('div', '', [
            'style' => 'background-image:url(' . ($model->file->getTransformationUrl('admin') ?: $model->file->getUrl()) . ');',
            'class' => 'thumb',
        ]);

        if ($hotspotCount = $model->getAttribute('hotspot_count')) {
            $badge = Html::tag('div', $hotspotCount, [
                'class' => 'btn btn-primary btn-sm',
                'style' => 'position: absolute; top: 5px; left: 5px;',
            ]);

            $content = Html::tag('div', $content . $badge, [
                'class' => 'active',
                'style' => 'position:relative',
            ]);
        }

        $isLink = Yii::$app->getUser()->can($model->isEntryAsset() ? 'entryAssetUpdate' : 'sectionAssetUpdate', [
            'asset' => $model,
        ]);

        return $isLink ? Html::a($content, ['/admin/cms/asset/update', 'id' => $model->id]) : $content;
    }
}