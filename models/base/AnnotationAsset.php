<?php

namespace davidhirtz\yii2\annotation\models\base;

use davidhirtz\yii2\annotation\models\queries\AnnotationQuery;
use davidhirtz\yii2\annotation\modules\admin\Module;
use davidhirtz\yii2\annotation\modules\admin\widgets\forms\AnnotationAssetActiveForm;
use davidhirtz\yii2\annotation\modules\admin\widgets\grid\base\AnnotationAnnotationAssetParentGridView;
use davidhirtz\yii2\annotation\modules\ModuleTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\media\models\AssetInterface;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\models\queries\FileQuery;
use davidhirtz\yii2\media\models\traits\AssetTrait;
use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\db\TypeAttributeTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\base\Widget;

/**
 * Class AnnotationAsset
 * @package davidhirtz\yii2\annotation\models\base
 * @see \davidhirtz\yii2\annotation\models\Asset
 *
 * @property int $id
 * @property int $annotation_id
 * @property int $file_id
 * @property int $position
 * @property string $content
 * @property string $alt_text
 * @property string $link
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property \davidhirtz\yii2\annotation\models\Annotation $annotation
 * @property File $file
 * @property User $updated
 *
 * @method static \davidhirtz\yii2\annotation\models\AnnotationAsset findOne($condition)
 */
class AnnotationAsset extends ActiveRecord implements AssetInterface
{
    use I18nAttributesTrait;
    use AssetTrait;
    use ModuleTrait;
    use StatusAttributeTrait;
    use TypeAttributeTrait;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [
                ['status', 'type'],
                'davidhirtz\yii2\skeleton\validators\DynamicRangeValidator',
                'skipOnEmpty' => false,
            ],
            [
                ['file_id', 'annotation_id'],
                'required',
            ],
            [
                ['annotation_id'],
                'filter',
                'filter' => 'intval',
            ],
            [
                ['annotation_id'],
                'davidhirtz\yii2\skeleton\validators\RelationValidator',
                'relation' => 'annotation',
            ],
            [
                ['file_id'],
                'davidhirtz\yii2\skeleton\validators\RelationValidator',
                'relation' => 'file',
            ],
            [
                $this->getI18nAttributesNames(['name', 'alt_text', 'link']),
                'string',
                'max' => 250,
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->annotation->asset_count = $this->findSiblings()->count();
            $this->annotation->update();

            $this->updateOrDeleteFileByAssetCount();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        if (!$this->annotation->isDeleted()) {
            $this->annotation->asset_count = $this->findSiblings()->count();
            $this->annotation->update();
        }

        $this->updateOrDeleteFileByAssetCount();
        parent::afterDelete();
    }

    /**
     * @return AnnotationQuery
     */
    public function getAnnotation()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Annotation::class, ['id' => 'annotation_id']);
    }

    /**
     * @return FileQuery
     */
    public function getFile(): FileQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function findSiblings()
    {
        return static::find()->where(['annotation_id' => $this->annotation_id]);
    }

    /**
     * @return ActiveQuery
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    /**
     * @param Annotation $annotation
     */
    public function populateAnnotationRelation($annotation)
    {
        $this->populateRelation('annotation', $annotation);
        $this->annotation_id = $annotation->id;
    }

    /**
     * @return array
     */
    public function getTrailParents()
    {
        return [$this->annotation];
    }

    /**
     * @return string
     */
    public function getTrailModelType(): string
    {
        return Yii::t('annotation', 'Asset');
    }

    /**
     * @return AnnotationAssetActiveForm|Widget
     */
    public function getActiveForm()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return static::getTypes()[$this->type]['activeForm'] ?? AnnotationAssetActiveForm::class;
    }

    /**
     * @return string
     */
    public function getParentGridView(): string
    {
        return AnnotationAnnotationAssetParentGridView::class;
    }

    /**
     * @return mixed|string
     */
    public function getParentName(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('annotation');
        return $module->name;
    }

    /**
     * @return string
     */
    public function getFileCountAttribute(): string
    {
        return 'annotation_asset_count';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'section_id' => Yii::t('annotation', 'Section'),
            'file_id' => Yii::t('media', 'File'),
            'alt_text' => Yii::t('annotation', 'Alt text'),
            'link' => Yii::t('annotation', 'Link'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('annotation_asset');
    }
}