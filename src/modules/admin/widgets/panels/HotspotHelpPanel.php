<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\modules\admin\widgets\panels;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use davidhirtz\yii2\cms\hotspot\modules\admin\controllers\HotspotController;
use davidhirtz\yii2\media\modules\admin\widgets\panels\traits\DuplicateButtonTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\panels\HelpPanel;

class HotspotHelpPanel extends HelpPanel
{
    use DuplicateButtonTrait;

    public ?Hotspot $model = null;

    public function init(): void
    {
        $this->content ??= $this->renderButtonToolbar($this->getButtons());
        parent::init();
    }

    /**
     * @see HotspotController::actionDuplicate()
     */
    protected function getButtons(): array
    {
        return array_filter([
            $this->getDuplicateButton(),
        ]);
    }
}
