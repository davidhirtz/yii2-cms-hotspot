<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\models\actions;

use Hirtz\Cms\hotspot\models\Hotspot;
use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Cms\Models\actions\DuplicateActiveRecord;
use Hirtz\Cms\Models\Asset;
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

    #[\Override]
    protected function beforeDuplicate(): bool
    {
        $this->duplicate->populateAssetRelation(!$this->asset || $this->asset->getIsNewRecord()
            ? $this->model->asset
            : $this->asset);


        $this->duplicate->asset_count = $this->model->asset_count;
        $this->duplicate->shouldUpdateAssetAfterInsert = $this->shouldUpdateAssetAfterInsert;

        return parent::beforeDuplicate();
    }

    #[\Override]
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
