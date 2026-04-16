<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Media\Models\Interfaces\AssetInterface;
use Hirtz\Skeleton\Html\Div;
use Stringable;
use yii\base\Model;

class AssetThumbnailColumn extends \Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn
{
    public function __construct(array $config = [])
    {
        $this->content ??= $this->getThumbnailWithHotspotCount(...);
        parent::__construct($config);
    }

    protected function getThumbnailWithHotspotCount(array|Model $model): string|Stringable
    {
        $content = $this->getThumbnail($model);
        $hotspotCount = $model instanceof AssetInterface ? $model->getAttribute('hotspot_count') : 0;

        if (!$content || !$hotspotCount) {
            return $content;
        }

        $badge = Div::make()
            ->text((string)$hotspotCount)
            ->addClass('badge')
            ->addStyle([
                'position' => 'absolute',
                'top' => '5px',
                'left' => '5px',
            ]);

        return Div::make()
            ->content($content)
            ->addContent($badge)
            ->addStyle([
                'position' => 'relative',
            ]);
    }
}
