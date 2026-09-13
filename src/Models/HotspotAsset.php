<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models;

use Hirtz\Media\Models\Asset;
use Hirtz\Skeleton\Models\CustomAttributes\CustomAttribute;
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

    #[Override]
    public static function getAdminControllerRoute(): string
    {
        return '/admin/hotspot/hotspot-asset';
    }

    #[Override]
    public function getPermissionName(): string
    {
        return $this->model->asset->getPermissionName();
    }

    #[Override]
    public function getTrailParents(): array
    {
        $hotspot = $this->model;

        return [$hotspot, ...$hotspot->getTrailParents()];
    }

    #[Override]
    public function getAdminType(): string
    {
        return Yii::t('hotspot', 'COMMON_HOTSPOT_ASSET');
    }
}
