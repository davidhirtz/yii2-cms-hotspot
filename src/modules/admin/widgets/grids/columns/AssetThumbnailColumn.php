<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\columns;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\skeleton\helpers\Html;
use Yii;

class AssetThumbnailColumn extends \davidhirtz\yii2\cms\modules\admin\widgets\grids\columns\AssetThumbnailColumn
{
    protected function renderDataCellContent($model, $key, $index): string
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

        $isLink = $model instanceof Asset && Yii::$app->getUser()->can($model->isEntryAsset() ? 'entryAssetUpdate' : 'sectionAssetUpdate', [
            'asset' => $model,
        ]);

        return $isLink ? Html::a($content, ['/admin/cms/asset/update', 'id' => $model->id]) : $content;
    }
}
