<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Forms\Traits\ActiveFormFieldsTrait;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Traits\AssetFieldsTrait;
use Hirtz\Skeleton\Widgets\Forms\ActiveForm;

/**
 * @property HotspotAsset $model
 */
class HotspotAssetActiveForm extends ActiveForm
{
    use ActiveFormFieldsTrait;
    use AssetFieldsTrait;

    #[\Override]
    public function configure(): void
    {
        $this->rows ??= [
            $this->getStatusField(),
            $this->getTypeField(),
            $this->getNameField(),
            $this->getContentField(),
            $this->getAltTextField(),
            $this->getLinkField(),
        ];

        parent::configure();
    }
}
