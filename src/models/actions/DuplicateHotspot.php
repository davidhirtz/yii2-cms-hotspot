<?php

namespace davidhirtz\yii2\cms\hotspot\models\actions;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\models\actions\DuplicateActiveRecord;
use davidhirtz\yii2\cms\models\Asset;
use Yii;

/**
 * @extends  DuplicateActiveRecord<Hotspot>
 */
class DuplicateHotspot extends DuplicateActiveRecord
{
    public function __construct(
        Hotspot $hotspot,
        protected ?Asset $asset = null,
        protected ?bool $shouldUpdateAssetAfterInsert = true,
        array $attributes = []
    ) {
        parent::__construct($hotspot, $attributes);
    }

    protected function beforeDuplicate(): bool
    {
        $this->duplicate->populateAssetRelation(!$this->asset || $this->asset->getIsNewRecord()
            ? $this->model->asset
            : $this->asset);


        $this->duplicate->asset_count = $this->model->asset_count;
        $this->duplicate->shouldUpdateAssetAfterInsert = $this->shouldUpdateAssetAfterInsert;

        return parent::beforeDuplicate();
    }

    protected function afterDuplicate(): void
    {
        parent::afterDuplicate();

        if ($this->model->asset_count) {
            $this->duplicateAssets();
        }
    }

    protected function duplicateAssets(): void
    {
        Yii::debug('Duplicating hotspot assets ...');

        $assets = $this->model->getAssets()
            ->with('file')
            ->all();

        $position = 0;

        foreach ($assets as $asset) {
            $duplicate = HotspotAsset::create();
            $duplicate->populateHotspotRelation($this->duplicate);
            $duplicate->populateFileRelation($asset->file);
            $duplicate->shouldUpdateHotspotAfterInsert = false;
            $duplicate->status = $asset->status;
            $duplicate->position = ++$position;
            $duplicate->insert();
        }
    }
}