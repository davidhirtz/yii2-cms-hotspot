<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\columns;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\modules\admin\widgets\grids\columns\Thumbnail;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\columns\LinkDataColumn;

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
