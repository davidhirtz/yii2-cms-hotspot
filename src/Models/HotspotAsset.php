<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models;

use Hirtz\Media\Models\Asset;
use Hirtz\Media\Models\File;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Override;
use Yii;

/**
 * @extends Asset<Hotspot>
 */
class HotspotAsset extends Asset
{
    #[Override]
    public static function getModelClass(): string
    {
        return Hotspot::class;
    }

    /**
     * The permission depends on the model the hotspot's own asset hangs on, so it is the record's, not the class's. One
     * whose hotspot is gone (no foreign key on `model_id`) is left to whoever manages files.
     */
    #[Override]
    public function getPermissionName(): string
    {
        if (!$this->isRelationPopulated('model')) {
            $this->populateRelation('model', Hotspot::findOne($this->model_id));
        }

        $hotspot = $this->getRelatedRecords()['model'] ?? null;

        return $hotspot instanceof Hotspot ? $hotspot->asset->getPermissionName() : File::AUTH_FILE;
    }

    #[Override]
    public static function getAdminControllerRoute(): string
    {
        return '/admin/hotspot/hotspot-asset';
    }

    /**
     * @return list<TrailModelInterface>
     */
    #[Override]
    public function getTrailParents(): array
    {
        $hotspot = $this->model;

        return [$hotspot, ...$hotspot->getTrailParents()];
    }

    #[Override]
    public function getAdminType(): string
    {
        return Yii::t('hotspot', 'HOTSPOT_ASSET_SINGULAR');
    }
}
