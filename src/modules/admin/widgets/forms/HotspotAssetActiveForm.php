<?php

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\cms\modules\admin\widgets\forms\ActiveForm;
use davidhirtz\yii2\cms\hotspot\models\HotspotAsset;
use davidhirtz\yii2\media\modules\admin\widgets\forms\traits\AssetFieldsTrait;

/**
 * @property HotspotAsset $model
 */
class HotspotAssetActiveForm extends ActiveForm
{
    use AssetFieldsTrait;

    public function init(): void
    {
        $this->fields ??= array_diff($this->getDefaultFieldNames(), [
            'file_id',
            'hotspot_id',
        ]);

        parent::init();
    }
}