<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models;

use Hirtz\Media\Models\Asset;
use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Skeleton\Models\CustomAttributes\CustomAttribute;
use Override;

/**
 * @property-read Hotspot $model {@see static::getModel()}
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
        return '/admin/hotspot-asset';
    }

    #[Override]
    public function getPermissionName(string $action): string
    {
        return $this->model->asset->getPermissionName($action);
    }

    #[Override]
    public function getModel(): Hotspot
    {
        /** @var Hotspot */
        return parent::getModel();
    }

    #[Override]
    public function getTrailParents(): array
    {
        $hotspot = $this->model;

        return [$hotspot, ...$hotspot->getTrailParents()];
    }

    #[Override]
    public function getTrailModelType(): string
    {
        return Lang::t('hotspot', 'COMMON_HOTSPOT_ASSET');
    }
}
