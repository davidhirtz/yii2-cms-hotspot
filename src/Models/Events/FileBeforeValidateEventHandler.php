<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Events;

use Hirtz\Media\Models\Asset;
use Hirtz\Media\Models\File;
use Yii;
use yii\db\Query;

/**
 * An upload replacing the file of an asset carrying hotspots keeps an image to place them on.
 */
readonly class FileBeforeValidateEventHandler
{
    public function __construct(protected File $file)
    {
        $this->handleEvent();
    }

    protected function handleEvent(): void
    {
        if ($this->file->getIsNewRecord() || !$this->file->upload || $this->file->hasPreview()) {
            return;
        }

        $hasHotspots = (new Query())
            ->from(Asset::tableName())
            ->where(['file_id' => $this->file->id])
            ->andWhere(['>', 'hotspot_count', 0])
            ->exists(File::getDb());

        if ($hasHotspots) {
            $this->file->addError('upload', Yii::t('hotspot', 'HOTSPOT_FILE_PREVIEW_ERROR'));
        }
    }
}
