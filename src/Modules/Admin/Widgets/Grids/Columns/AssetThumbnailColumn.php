<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Media\Models\Interfaces\AssetInterface;
use Hirtz\Skeleton\Html\Div;
use Override;
use Stringable;
use yii\base\Model;

class AssetThumbnailColumn extends \Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn
{
    #[Override]
    protected function getValue(array|Model $model, string|int $key, int $index): string|Stringable
    {
        $content = parent::getValue($model, $key, $index);

        if ($content) {
            $hotspotCount = $model instanceof AssetInterface ? $model->getAttribute('hotspot_count') : 0;

            if ($hotspotCount) {
                $badge = Div::make()
                    ->text($hotspotCount)
                    ->addClass('btn btn-primary btn-sm')
                    ->addStyle([
                        'position' => 'absolute',
                        'top' => '5px',
                        'left' => '5px',
                    ]);

                $content = Div::make()
                    ->content($content)
                    ->addContent($badge)
                    ->addClass('active')
                    ->addStyle('position:relative;');
            }
        }

        return $content;
    }
}
