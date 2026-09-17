<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms\Fields;

use Hirtz\Cms\Hotspot\Assets\HotspotAdminAssetBundle;
use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Cms\Hotspot\Modules\ModuleTrait;
use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Widgets\Alert;
use Override;
use Stringable;
use Yii;

class AssetPreviewField extends \Hirtz\Media\Modules\Admin\Widgets\Forms\Fields\AssetPreviewField
{
    use ModuleTrait;

    /**
     * @var list<Hotspot>
     */
    protected array $hotspots;

    #[Override]
    protected function configure(): void
    {
        if ($this->allowsHotspots()) {
            $this->hotspots = array_values($this->getHotspots());
            $this->registerClientScript();
        }

        parent::configure();
    }

    #[Override]
    protected function renderContent(): string|Stringable
    {
        $content = parent::renderContent();

        if ($this->allowsHotspots()) {
            $alert = Alert::make()
                ->info()
                ->text(Yii::t('hotspot', 'ASSET_PREVIEW_DOUBLE_CLICK'));

            $content = $alert . Div::make()
                    ->attribute('hx-select:inherited', '#wrap')
                    ->attribute('hx-target:inherited', '#wrap')
                    ->content($content);
        }

        return $content;
    }

    protected function allowsHotspots(): bool
    {
        return $this->asset->file->hasPreview() && static::getModule()->allowsHotspots($this->asset);
    }

    protected function registerClientScript(): void
    {
        $bundle = HotspotAdminAssetBundle::register($this->view);

        $this->view->registerJsModule($bundle->getModuleFilename(), [
            'formName' => Hotspot::instance()->formName(),
            'url' => Url::toRoute(['/admin/hotspot/hotspot/create', 'id' => $this->asset->id]),
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
