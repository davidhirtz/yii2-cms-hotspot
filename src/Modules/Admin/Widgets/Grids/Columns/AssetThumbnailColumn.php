<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Cms\Models\Asset;
use Hirtz\Skeleton\Html\Div;
use Stringable;

/**
 * @template T of Asset
 * @extends \Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn<T>
 */
class AssetThumbnailColumn extends \Hirtz\Cms\Modules\Admin\Widgets\Grids\Columns\AssetThumbnailColumn
{
    public function __construct(array $config = [])
    {
        $this->content ??= $this->getThumbnailWithHotspotCount(...);
        parent::__construct($config);
    }

    /**
     * @param T $model
     */
    protected function getThumbnailWithHotspotCount(Asset $model): string|Stringable
    {
        $content = $this->getThumbnail($model);
        $hotspotCount = $model->getAttribute('hotspot_count');

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
