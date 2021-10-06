<?php

namespace davidhirtz\yii2\annotation\modules\admin\controllers;

use davidhirtz\yii2\annotation\models\AnnotationAsset;
use davidhirtz\yii2\annotation\models\Entry;
use davidhirtz\yii2\annotation\modules\admin\controllers\traits\AnnotationAssetTrait;
use davidhirtz\yii2\annotation\modules\admin\controllers\traits\AnnotationTrait;
use davidhirtz\yii2\annotation\modules\admin\controllers\traits\EntryTrait;
use davidhirtz\yii2\annotation\modules\admin\controllers\traits\SectionTrait;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\annotation\models\Asset;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\modules\admin\controllers\traits\FileTrait;
use davidhirtz\yii2\media\modules\admin\data\FileActiveDataProvider;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class AnnotationAssetController
 * @package davidhirtz\yii2\annotation\modules\admin\controllers
 */
class AnnotationAssetController extends Controller
{
    use AnnotationTrait;
    use ModuleTrait;
    use FileTrait;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create', 'delete', 'update'],
                        'roles' => ['annotationUpdate'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * @param int $annotation
     * @param int|null $file
     * @param int|null $folder
     * @return string|Response
     */
    public function actionCreate($annotation, $file = null, $folder = null)
    {
        $annotation = $this->findAnnotation($annotation, 'annotationUpdate');

        $request = Yii::$app->getRequest();
        $user = Yii::$app->getUser();

        if(!($file = File::findOne($file) ?: $this->insertFileFromRequest($folder))) {
            return '';
        }

        $asset = new AnnotationAsset();
        $asset->pop = $entry;
        $asset->section_id = $section;
        $asset->populateFileRelation($file);

        if (!$user->can($asset->isEntryAsset() ? 'entryAssetCreate' : 'sectionAssetCreate', ['asset' => $asset])) {
            throw new ForbiddenHttpException();
        }

        if (!$asset->insert()) {
            $errors = $asset->getFirstErrors();
            throw new BadRequestHttpException(reset($errors));
        }

        if ($request->getIsAjax()) {
            return '';
        }

        $this->success(Yii::t('annotation', 'The asset was added.'));
        return $this->redirectToParent($asset);
//
//
//        $asset->file_id = $file->id;
//
//
//        if (!$asset->insert()) {
//            $errors = $asset->getFirstErrors();
//            throw new BadRequestHttpException(reset($errors));
//        }
//
//        if ($request->getIsAjax()) {
//            return '';
//        }
//
//        $this->success($isNew ? Yii::t('annotation', 'The asset was created.') : Yii::t('annotation', 'The asset was added.'));
        return $this->redirect(['/admin/annotation/update', 'id' => $asset->annotation_id]);
    }

    /**
     * @param int $id
     * @return string|Response
     */
    public function actionUpdate(int $id)
    {
        $asset = $this->findAsset($id);

        if ($asset->load(Yii::$app->getRequest()->post())) {
            if ($asset->update()) {
                $this->success(Yii::t('annotation', 'The asset was updated.'));
            }

            if (!$asset->hasErrors()) {
                return $this->redirect(['/admin/annotation/update', 'id' => $asset->annotation_id]);
            }
        }

        /** @noinspection MissedViewInspection */
        return $this->render('update', [
            'asset' => $asset,
        ]);
    }

    /**
     * @param int $id
     * @return string|Response
     */
    public function actionDelete($id)
    {
        $asset = $this->findAsset($id);

        if ($asset->delete()) {
            if (Yii::$app->getRequest()->getIsAjax()) {
                return '';
            }

            $this->success(Yii::t('annotation', 'The asset was deleted.'));
            return $this->redirect(['/admin/annotation/update', 'id' => $asset->annotation_id]);
        }

        $errors = $asset->getFirstErrors();
        throw new BadRequestHttpException(reset($errors));
    }

    /**
     * @param int $id
     * @param string|null $permissionName
     * @return AnnotationAsset
     */
    protected function findAsset($id, $permissionName = null)
    {
        if (!$asset = AnnotationAsset::findOne((int)$id)) {
            throw new NotFoundHttpException();
        }

        if (!Yii::$app->getUser()->can('annotationUpdate', ['annotation' => $asset->annotation])) {
            throw new ForbiddenHttpException();
        }

        return $asset;
    }
}