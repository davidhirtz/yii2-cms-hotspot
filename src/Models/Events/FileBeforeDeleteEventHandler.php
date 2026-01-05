<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Events;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Media\Models\File;
use Yii;
use yii\base\ModelEvent;

readonly class FileBeforeDeleteEventHandler
{
    public function __construct(protected ModelEvent $event, protected File $file)
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
