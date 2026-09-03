<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms\Fields;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Cms\Hotspot\Assets\HotspotAdminAssetBundle;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\Admin\Module;
use Hirtz\Cms\Models\Asset;
use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Widgets\Alert;
use Override;
use Stringable;
use Yii;

class AssetPreviewField extends \Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreviewField
{
    protected array $hotspots;

    #[Override]
    protected function configure(): void
    {
        if ($this->hasHotspotsEnabled()) {
            $this->hotspots = $this->getHotspots();
            $this->registerClientScript();
        }

        parent::configure();
    }

    #[Override]
    protected function renderContent(): string|Stringable
    {
        $content = parent::renderContent();

        if ($this->hasHotspotsEnabled()) {
            $alert = Alert::make()
                ->info()
                ->text(Lang::t('hotspot', 'ASSET_PREVIEW_DOUBLE_CLICK'));

            $content = $alert . Div::make()
                    ->attribute('hx-select', '#wrap')
                    ->attribute('hx-target', '#wrap')
                    ->content($content);
        }

        return $content;
    }

    protected function hasHotspotsEnabled(): bool
    {
        if (!$this->asset instanceof Asset || !$this->asset->file->hasPreview()) {
            return false;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');

        return $this->asset->isSectionAsset()
            ? $module->enableSectionAssetHotspots
            : $module->enableEntryAssetHotspots;
    }

    protected function registerClientScript(): void
    {
        $bundle = HotspotAdminAssetBundle::register($this->view);

        $this->view->registerJsModule($bundle->getModuleFilename(), [
            'formName' => Hotspot::instance()->formName(),
            'url' => Url::toRoute(['/admin/hotspot/create', 'id' => $this->asset->id]),
            'hotspots' => $this->hotspots,
        ]);
    }

    /**
     * @return Hotspot[]
     */
    protected function getHotspots(): array
    {
        if (!$this->asset->isRelationPopulated('hotspots')) {
            $this->asset->populateRelation('hotspots', $this->asset->getAttribute('hotspot_count')
                ? Hotspot::find()
                    ->where(['asset_id' => $this->asset->id])
                    ->orderBy(['position' => SORT_ASC])
                    ->all()
                : []);
        }

        return $this->asset->getRelatedRecords()['hotspots'] ?? [];
    }
}
