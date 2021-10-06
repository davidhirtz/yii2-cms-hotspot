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
     * @param string|null $permissionName
     * @return Hotspot
     */
    protected function findHotspot($id, $permissionName = null)
    {
        if (!$hotspot = Hotspot::findOne((int)$id)) {
            throw new NotFoundHttpException();
        }

        if ($permissionName && !Yii::$app->getUser()->can($permissionName, ['hotspot' => $hotspot])) {
            throw new ForbiddenHttpException();
        }

        return $hotspot;
    }
}