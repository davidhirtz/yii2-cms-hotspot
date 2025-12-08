<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Modules\Admin\Widgets\Panels;

use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Grids\HotspotAssetParentGridView;
use Hirtz\Media\models\File;
use Hirtz\Skeleton\Widgets\Bootstrap\Panel;
use Yii;
use yii\base\Widget;

class HotspotAssetFilePanel extends Widget
{
    public File $file;

    public function run(): void
    {
        foreach (HotspotAsset::instance()->getFileCountAttributeNames() as $language => $attributeName) {
            if ($this->file->$attributeName) {
                echo Panel::widget([
                    'title' => $this->getTitle($language),
                    'content' => HotspotAssetParentGridView::widget([
                        'file' => $this->file,
                        'language' => $language,
                    ]),
                ]);
            }
        }
    }

    protected function getTitle(string $language): string
    {
        $title = Yii::t('hotspot', 'Hotspots');

        if ($language != Yii::$app->language) {
            $title .= ' (' . strtoupper($language) . ')';
        }

        return $title;
    }
}
