<?php

namespace davidhirtz\yii2\hotspot\modules\admin\controllers\traits;

use davidhirtz\yii2\hotspot\models\Hotspot;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Trait HotspotTrait
 * @package davidhirtz\yii2\hotspot\modules\admin\controllers\traits
 */
trait HotspotTrait
{
    /**
     * @param int $id
     * @return Hotspot
     */
    protected function findHotspot($id)
    {
        if (!$hotspot = Hotspot::findOne((int)$id)) {
            throw new NotFoundHttpException();
        }

        if (!Yii::$app->getUser()->can($hotspot->asset->isEntryAsset() ? 'entryAssetUpdate' : 'sectionAssetUpdate', ['asset' => $hotspot->asset])) {
            throw new ForbiddenHttpException();
        }

        return $hotspot;
    }
}