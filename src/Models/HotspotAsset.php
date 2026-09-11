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

    /**
     * A hotspot has no permissions of its own; it is edited by whoever may edit the asset it sits on.
     */
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

    /**
     * @return list<CustomAttribute>
     */
    #[Override]
    protected function getDefaultCustomAttributes(): array
    {
        return array_values(array_filter(
            parent::getDefaultCustomAttributes(),
            fn (CustomAttribute $definition): bool => $definition->name !== 'embed_url'
        ));
    }

    #[Override]
    public function getTrailParents(): array
    {
        $hotspot = $this->model;

        return [$hotspot, ...(array)$hotspot->getTrailParents()];
    }

    #[Override]
    public function getTrailModelType(): string
    {
        return Lang::t('hotspot', 'COMMON_HOTSPOT_ASSET');
    }
}
