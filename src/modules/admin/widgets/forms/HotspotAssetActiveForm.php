<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Modules\Admin\Widgets\Forms;

use Hirtz\Cms\hotspot\models\HotspotAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Forms\ActiveForm;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Traits\AssetFieldsTrait;

/**
 * @property HotspotAsset $model
 */
class HotspotAssetActiveForm extends ActiveForm
{
    use AssetFieldsTrait;

    public function init(): void
    {
        $this->fields ??= [
            'status',
            'type',
            'name',
            'content',
            'alt_text',
            'link',
        ];

        parent::init();
    }
}
