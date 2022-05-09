<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\cms\modules\admin\widgets\forms\ActiveForm;
use davidhirtz\yii2\cms\hotspot\models\base\HotspotAsset;
use davidhirtz\yii2\media\modules\admin\widgets\forms\traits\AssetFieldsTrait;

/**
 * HotspotAssetActiveForm is a widget that builds an interactive HTML form for {@see HotspotAsset}. By default, it
 * implements fields for all safe attributes defined in the model.
 *
 * @property HotspotAsset $model
 */
class HotspotAssetActiveForm extends ActiveForm
{
    use AssetFieldsTrait;

    /**
     * @var bool
     */
    public $hasStickyButtons = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fields = $this->fields ?: array_diff($this->getDefaultFieldNames(), [
            'file_id',
            'hotspot_id',
        ]);

        parent::init();
    }
}