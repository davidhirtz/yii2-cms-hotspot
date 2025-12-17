<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Cms\Models\Asset;
use Hirtz\Skeleton\Helpers\Html;

class AssetThumbnailColumn extends \Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn
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
