<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\modules\admin\widgets\grids\columns;

use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Media\modules\admin\widgets\grids\columns\Thumbnail;
use Hirtz\Skeleton\modules\admin\widgets\grids\columns\LinkDataColumn;

class HotspotAssetThumbnailColumn extends LinkDataColumn
{
    public $headerOptions = ['style' => 'width:150px'];

    public function init(): void
    {
        if (!is_callable($this->content)) {
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
