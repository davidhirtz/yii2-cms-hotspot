<?php

namespace davidhirtz\yii2\annotation\models\base;

use davidhirtz\yii2\annotation\models\queries\AnnotationQuery;
use davidhirtz\yii2\annotation\modules\admin\widgets\forms\AnnotationActiveForm;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\cms\models\queries\AssetQuery;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\media\models\AssetParentInterface;
use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\db\TypeAttributeTrait;
use davidhirtz\yii2\skeleton\models\queries\UserQuery;
use davidhirtz\yii2\skeleton\models\User;
use Yii;

/**
 * Class Annotation
 * @package davidhirtz\yii2\annotation\models\base
 *
 * @property int $id
 * @property int $status
 * @property int $type
 * @property int $asset_id
 * @property string $name
 * @property string $content
 * @property string $link
 * @property float $x
 * @property float $y
 * @property int $position
 * @property int $asset_count
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 * @property Asset $asset
 * @property \davidhirtz\yii2\annotation\models\AnnotationAsset[] $annotationAssets
 * @method static \davidhirtz\yii2\annotation\models\Annotation findOne($condition)
 */
class Annotation extends ActiveRecord implements AssetParentInterface
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use StatusAttributeTrait;
    use TypeAttributeTrait;

    /**
     * @var mixed used when $contentType is set to "html". use array with the first value containing the
     * validator class, following keys can be used to configure the validator, string containing the class
     * name or false for disabling the validation.
     */
    public $htmlValidator = 'davidhirtz\yii2\skeleton\validators\HtmlValidator';

    /**
     * @var string|false the content type, "html" enables html validators and WYSIWYG editor
     */
    public $contentType = 'html';

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'DateTimeBehavior' => 'davidhirtz\yii2\datetime\DateTimeBehavior',
            'TrailBehavior' => 'davidhirtz\yii2\skeleton\behaviors\TrailBehavior',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), $this->getI18nRules([
            [
                ['status', 'type'],
                'davidhirtz\yii2\skeleton\validators\DynamicRangeValidator',
                'skipOnEmpty' => false,
            ],
            [
                ['asset_id'],
                'required',
            ],
            [
                ['asset_id'],
                'filter',
                'filter' => 'intval',
            ],
            [
                ['asset_id'],
                'davidhirtz\yii2\skeleton\validators\RelationValidator',
                'relation' => 'asset',
                'required' => true,
            ],
            [
                ['name', 'content', 'link'],
                'filter',
                'filter' => 'trim',
            ],
            [
                ['name', 'link'],
                'string',
                'max' => 250,
            ],
            array_merge(
                [$this->getI18nAttributesNames(['content'])],
                (array)($this->contentType == 'html' && $this->htmlValidator ? $this->htmlValidator : 'safe')
            ),
        ]));
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        if ($this->status === null) {
            $this->status = static::STATUS_DEFAULT;
        }

        if ($this->type === null) {
            $this->type = static::TYPE_DEFAULT;
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        // Disable annotation move / clone for now ...
        if (!$this->getIsNewRecord() && $this->isAttributeChanged('asset_id')) {
            $this->addInvalidAttributeError('asset_id');
        }

        parent::afterValidate();
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->recalculateAssetAnnotationCount();
            $this->asset->update();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->asset_count) {
            foreach ($this->annotationAssets as $asset) {
                $asset->delete();
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        if (!$this->asset->isDeleted()) {
            $this->recalculateAssetAnnotationCount();
            $this->asset->update();
        }

        parent::afterDelete();
    }

    /**
     * @return AssetQuery
     */
    public function getAsset()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAssets()
    {
        return $this->hasMany(\davidhirtz\yii2\annotation\models\AnnotationAsset::class, ['annotation_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('annotation');
    }

    /**
     * @return UserQuery
     */
    public function getUpdated(): UserQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(User::class, ['id' => 'updated_by_user_id']);
    }

    /**
     * @return AnnotationQuery
     */
    public function findSiblings()
    {
        return static::find()->where(['asset_id' => $this->asset_id]);
    }

    /**
     * @return AnnotationQuery
     */
    public static function find()
    {
        return Yii::createObject(AnnotationQuery::class, [get_called_class()]);
    }

    /**
     * @param Asset $asset
     */
    public function populateAssetRelation($asset)
    {
        $this->populateRelation('asset', $asset);
        $this->asset_id = $asset->id ?? null;
    }

    /**
     * Recalculates the asset's annotation count.
     */
    protected function recalculateAssetAnnotationCount()
    {
        $this->asset->setAttribute('annotation_count', (int)static::findSiblings()->count());
    }

    /**
     * @return array
     */
    public function getTrailAttributes(): array
    {
        return array_diff($this->attributes(), [
            'position',
            'asset_count',
            'updated_by_user_id',
            'updated_at',
            'created_at',
        ]);
    }

    /**
     * @return array
     */
    public function getTrailParents()
    {
        return [$this->asset];
    }

    /**
     * @return string
     */
    public function getTrailModelName()
    {
        if ($this->id) {
            return $this->getI18nAttribute('name') ?: Yii::t('skeleton', '{model} #{id}', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    /**
     * @return string
     */
    public function getTrailModelType(): string
    {
        return Yii::t('annotation', 'Annotation');
    }

    /**
     * @return AnnotationActiveForm
     */
    public function getActiveForm()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return static::getTypes()[$this->type]['activeForm'] ?? AnnotationActiveForm::class;
    }

    /**
     * @return array|false
     */
    public function getAdminRoute()
    {
        return $this->id ? ['/admin/annotation/update', 'id' => $this->id] : false;
    }

    /**
     * @return mixed|string
     */
    public function getHtmlId()
    {
        return $this->getI18nAttribute('slug') ?: ('annotation-' . $this->id);
    }

    /**
     * @return bool
     */
    public function hasAssetsEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'asset_id' => Yii::t('annotation', 'Asset'),
            'link' => Yii::t('annotation', 'Link'),
            'annotation_count' => Yii::t('annotation', 'Annotations'),
        ]);
    }

    /**
     * @return string
     */
    public function formName()
    {
        return 'Annotation';
    }

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return static::getModule()->getTableName('annotation');
    }
}