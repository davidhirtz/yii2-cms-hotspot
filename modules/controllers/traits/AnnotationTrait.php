<?php

namespace davidhirtz\yii2\annotation\modules\admin\controllers\traits;

use davidhirtz\yii2\annotation\models\Annotation;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Trait AnnotationTrait
 * @package davidhirtz\yii2\annotation\modules\admin\controllers\traits
 */
trait AnnotationTrait
{
    /**
     * @param int $id
     * @param string|null $permissionName
     * @return Annotation
     */
    protected function findAnnotation($id, $permissionName = null)
    {
        if (!$annotation = Annotation::findOne((int)$id)) {
            throw new NotFoundHttpException();
        }

        if ($permissionName && !Yii::$app->getUser()->can($permissionName, ['annotation' => $annotation])) {
            throw new ForbiddenHttpException();
        }

        return $annotation;
    }
}