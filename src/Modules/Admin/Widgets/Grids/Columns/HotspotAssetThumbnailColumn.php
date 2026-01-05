<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Columns\Thumbnail;
use Hirtz\Skeleton\Modules\Admin\Widgets\Grids\Columns\LinkDataColumn;

class HotspotAssetThumbnailColumn extends LinkDataColumn
{
    public $headerOptions = ['style' => 'width:150px'];

    public function init(): void
    {
        if ($this->content === null) {
            $this->content = $this->renderThumbnail(...);
        }

        parent::init();
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    protected function renderThumbnail(HotspotAsset $model, int $key, int $index): string
    {
        return Thumbnail::widget(['file' => $model->file]);
    }
}
