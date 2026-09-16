<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models;

use Hirtz\Media\Models\Asset;
use Hirtz\Skeleton\Models\CustomAttributes\CustomAttribute;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Override;
use Yii;

/**
 * @extends Asset<Hotspot>
 */
class HotspotAsset extends Asset
{
    /**
     * The marker that hides the hotspot panel, listed among a type's hidden fields.
     */
    final public const string FIELD_HOTSPOTS = 'hotspots';

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

    /**
     * A hotspot asset is a marker on an image: it is never an embed, and it is never the element whose loading
     * priority the page tunes — the asset the hotspot sits on is.
     *
     * @return list<CustomAttribute>
     */
    #[Override]
    protected function getDefaultCustomAttributes(): array
    {
        $names = ['name', 'content', 'alt_text', 'link'];

        return array_values(array_filter(
            parent::getDefaultCustomAttributes(),
            static fn (CustomAttribute $definition): bool => in_array($definition->name, $names, true),
        ));
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
        return Yii::t('hotspot', 'COMMON_HOTSPOT_ASSET');
    }
}
