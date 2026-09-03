<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Modules\ModuleTrait;
use Hirtz\Media\Modules\Admin\Widgets\Grids\Interfaces\FileRelationGridContainerInterface;
use Hirtz\Media\Traits\FilePropertyTrait;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;
use Hirtz\Skeleton\Widgets\Grids\Traits\GridTrait;
use Hirtz\Skeleton\Widgets\Widget;
use Stringable;
use Yii;

class FileHotspotAssetGridContainer extends Widget implements FileRelationGridContainerInterface
{
    use FilePropertyTrait;
    use GridTrait;
    use ModuleTrait;

    protected function renderContent(): string|Stringable
    {
        $content = '';

        foreach (HotspotAsset::instance()->getFileCountAttributeNames() as $language => $attributeName) {
            if ($this->file->$attributeName) {
                $content .= GridContainer::make()
                    ->title($this->getTitle($language))
                    ->grid(FileHotspotAssetGrid::make()
                        ->file($this->file)
                        ->language($language));
            }
        }

        return $content;
    }

    protected function getTitle(string $language): string
    {
        $title = Lang::t('hotspot', 'FILE_HOTSPOT_ASSET_GRID_CONTAINER_HOTSPOTS');

        if ($language !== Yii::$app->language && self::getModule()->enableI18nTables) {
            $title .= ' (' . strtoupper($language) . ')';
        }

        return $title;
    }
}
