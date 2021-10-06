<?php

namespace davidhirtz\yii2\hotspot\modules\admin\widgets\forms;

use davidhirtz\yii2\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ContentFieldTrait;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\DynamicRangeDropdown;

/**
 * Class HotspotActiveForm
 * @package davidhirtz\yii2\hotspot\modules\admin\widgets\forms
 *
 * @property Hotspot $model
 */
class HotspotActiveForm extends ActiveForm
{
    use ModuleTrait;
    use \davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
    use ContentFieldTrait;

    /**
     * @var bool
     */
    public $hasStickyButtons = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!$this->fields) {
            $this->fields = [
                ['status', DynamicRangeDropdown::class],
                ['name', DynamicRangeDropdown::class],
                'name',
                'content',
                'link',
            ];
        }

        parent::init();
    }
}