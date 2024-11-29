<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\columns;

use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\skeleton\helpers\Html;

class AssetThumbnailColumn extends \davidhirtz\yii2\cms\modules\admin\widgets\grids\columns\AssetThumbnailColumn
{
    protected function renderThumbnail(Asset $model, int $key, int $index): string
    {
        $content = parent::renderThumbnail($model, $key, $index);

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

        return $content;
    }
}
