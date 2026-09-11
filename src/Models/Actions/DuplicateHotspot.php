<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models\Actions;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Models\Actions\DuplicateActiveRecord;
use Hirtz\Media\Models\Actions\Traits\DuplicateAssetsTrait;
use Hirtz\Media\Models\Asset;
use Override;

/**
 * @extends  DuplicateActiveRecord<Hotspot>
 */
class DuplicateHotspot extends DuplicateActiveRecord
{
    use DuplicateAssetsTrait;

    public function __construct(
        Hotspot $hotspot,
        protected ?Asset $asset = null,
        protected ?bool $shouldUpdateAssetAfterInsert = true,
        array $attributes = []
    ) {
        parent::__construct($hotspot, $attributes);
    }

    #[Override]
    protected function beforeDuplicate(): bool
    {
        $this->duplicate->populateAssetRelation(!$this->asset || $this->asset->getIsNewRecord()
            ? $this->model->asset
            : $this->asset);


        $this->duplicate->asset_count = $this->model->asset_count;
        $this->duplicate->shouldUpdateAssetAfterInsert = $this->shouldUpdateAssetAfterInsert;

        return parent::beforeDuplicate();
    }

    #[Override]
    protected function afterDuplicate(): void
    {
        parent::afterDuplicate();

        if ($this->model->asset_count) {
            $this->duplicateAssets();
        }
    }

    /**
     * @return Asset[]
     */
    protected function getAssets(): array
    {
        return $this->model->getAssets()
            ->with('file')
            ->all();
    }
}
