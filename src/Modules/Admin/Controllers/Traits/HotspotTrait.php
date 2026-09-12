<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Modules\Admin\Controllers\Traits;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

trait HotspotTrait
{
    protected function findHotspot(int $id): Hotspot
    {
        if (!$hotspot = Hotspot::findOne($id)) {
            throw new NotFoundHttpException();
        }

        $asset = $hotspot->asset;

        if (!$this->webuser->can($asset->getPermissionName('update'), ['asset' => $asset])) {
            throw new ForbiddenHttpException();
        }

        return $hotspot;
    }
}
