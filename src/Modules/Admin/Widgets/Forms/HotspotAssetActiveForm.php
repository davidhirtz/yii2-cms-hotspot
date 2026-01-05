<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Widgets\Forms;

use Hirtz\Cms\Hotspot\Models\HotspotAsset;
use Hirtz\Cms\Modules\Admin\Widgets\Forms\Traits\ActiveFormFieldsTrait;
use Hirtz\Media\Modules\Admin\Widgets\Forms\Traits\AssetFieldsTrait;
use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Stringable;

/**
 * @property HotspotAsset $model
 */
class HotspotAssetActiveForm extends ActiveForm
{
    use ActiveFormFieldsTrait;
    use AssetFieldsTrait;

    public function init(): void
    {
        $this->fields ??= [
            $this->getStatusField(),
            $this->getTypeField(),
            $this->getNameField(),
            $this->getContentField(),
            $this->getAltTextField(),
            $this->getLinkField(),
        ];
    }

    protected function getLinkField(): ?Stringable
    {
        return InputField::make()
            ->property('link');
    }
}
