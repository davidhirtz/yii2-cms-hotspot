<?php

declare(strict_types=1);

namespace davidhirtz\yii2\cms\hotspot\modules\admin\controllers\traits;

use davidhirtz\yii2\cms\hotspot\models\Hotspot;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

trait HotspotTrait
{
    protected function findHotspot(int $id): Hotspot
    {
        if (!$hotspot = Hotspot::findOne($id)) {
            throw new NotFoundHttpException();
        }

        $permissionName = $hotspot->asset->isEntryAsset() ? 'entryAssetUpdate' : 'sectionAssetUpdate';

        if (!Yii::$app->getUser()->can($permissionName, ['asset' => $hotspot->asset])) {
            throw new ForbiddenHttpException();
        }

        return $hotspot;
    }
}
