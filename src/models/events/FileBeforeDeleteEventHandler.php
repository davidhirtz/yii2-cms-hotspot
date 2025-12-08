<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\models\events;

use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Media\models\File;
use Yii;
use yii\base\ModelEvent;

class FileBeforeDeleteEventHandler
{
    public function __construct(protected readonly ModelEvent $event, protected readonly File $file)
    {
        $this->handleEvent();
    }

    protected function handleEvent(): void
    {
        $i18n = Yii::$app->getI18n();

        foreach (HotspotAsset::instance()->getFileCountAttributeNames() as $language => $attributeName) {
            if ($this->file->$attributeName) {
                $i18n->callback($language, $this->deleteRelatedAssets(...));
            }
        }
    }

    protected function deleteRelatedAssets(): void
    {
        Yii::debug('Deleting related hotspot assets before deleting file ...');

        $assets = HotspotAsset::find()
            ->andWhere(['file_id' => $this->file->id])
            ->all();

        foreach ($assets as $asset) {
            $asset->delete();
        }
    }
}
