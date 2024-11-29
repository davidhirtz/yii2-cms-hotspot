<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\panels;

use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\HotspotAssetParentGridView;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
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
