<?php

namespace davidhirtz\yii2\cms\hotspot\models\base;

use davidhirtz\yii2\cms\models\base\ActiveRecord;
use davidhirtz\yii2\cms\hotspot\models\queries\HotspotAssetQuery;
use davidhirtz\yii2\cms\hotspot\models\queries\HotspotQuery;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\forms\HotspotAssetActiveForm;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grid\HotspotAssetParentGridView;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\media\models\AssetInterface;
use davidhirtz\yii2\media\models\File;
use davidhirtz\yii2\media\models\queries\FileQuery;
use davidhirtz\yii2\media\models\traits\AssetTrait;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use davidhirtz\yii2\skeleton\models\User;
use davidhirtz\yii2\skeleton\validators\RelationValidator;
use Yii;
use yii\base\Widget;

/**
 * The base implementation of the hotspot module HotspotAsset class.
 * @see \davidhirtz\yii2\cms\hotspot\models\HotspotAsset
 *
 * @property int $id
 * @property int $hotspot_id
 * @property int $file_id
 * @property int $position
 * @property string $name
 * @property string $content
 * @property string $alt_text
 * @property string $link
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property \davidhirtz\yii2\cms\hotspot\models\Hotspot $hotspot
 * @property File $file
 * @property User $updated
 *
 * @method static \davidhirtz\yii2\cms\hotspot\models\HotspotAsset findOne($condition)
 */
class HotspotAsset extends ActiveRecord implements AssetInterface
{
    use AssetTrait;

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => [
                'class' => TrailBehavior::class,
                'modelClass' => static::class . (static::getModule()->enableI18nTables ? ('::' . Yii::$app->language) : ''),
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [
                ['file_id', 'hotspot_id'],
                'required',
            ],
            [
                ['hotspot_id'],
                'filter',
                'filter' => 'intval',
            ],
            [
                ['hotspot_id'],
                RelationValidator::class,
                'relation' => 'hotspot',
            ],
            [
                ['file_id'],
                RelationValidator::class,
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
     * @return bool
     */
    public function beforeValidate()
    {
        $this->status ??= static::STATUS_DEFAULT;
        $this->type ??= static::TYPE_DEFAULT;

        if ($this->autoplayLinkAttributeName) {
            $this->validateAutoplayLink();
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        $this->attachBehaviors([
            'BlameableBehavior' => 'davidhirtz\yii2\skeleton\behaviors\BlameableBehavior',
            'TimestampBehavior' => 'davidhirtz\yii2\skeleton\behaviors\TimestampBehavior',
        ]);

        if ($this->position === null) {
            $this->position = $this->getMaxPosition() + 1;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->hotspot->asset_count = $this->findSiblings()->count();
            $this->hotspot->update();

            $this->updateOrDeleteFileByAssetCount();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        if (!$this->hotspot->isDeleted()) {
            $this->hotspot->asset_count = $this->findSiblings()->count();
            $this->hotspot->update();
        }

        $this->updateOrDeleteFileByAssetCount();
        parent::afterDelete();
    }

    /**
     * @return HotspotQuery
     */
    public function getHotspot()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Hotspot::class, ['id' => 'hotspot_id']);
    }

    /**
     * @return FileQuery
     */
    public function getFile()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    /**
     * @return HotspotAssetQuery
     */
    public function findSiblings()
    {
        return static::find()->where(['hotspot_id' => $this->hotspot_id]);
    }

    /**
     * @return HotspotAssetQuery
     */
    public static function find()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::createObject(HotspotAssetQuery::class, [get_called_class()]);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function clone($attributes = [])
    {
        $hotspot = ArrayHelper::remove($attributes, 'hotspot');

        $clone = new static();
        $clone->setAttributes(array_merge($this->getAttributes($this->safeAttributes()), $attributes));

        if ($hotspot) {
            $clone->populateHotspotRelation($hotspot);
        }

        $clone->insert();

        return $clone;
    }

    /**
     * @param Hotspot $hotspot
     */
    public function populateHotspotRelation($hotspot)
    {
        $this->populateRelation('hotspot', $hotspot);
        $this->hotspot_id = $hotspot->id;
    }

    /**
     * @return int
     */
    public function getMaxPosition(): int
    {
        return (int)$this->findSiblings()->max('[[position]]');
    }

    /**
     * @return false
     */
    public function getRoute()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getAdminRoute()
    {
        return ['/admin/hotspot-asset/update', 'id' => $this->id];
    }

    /**
     * @return array
     */
    public function getTrailParents()
    {
        return $this->hotspot->asset->isSectionAsset() ? [$this->hotspot, $this->hotspot->asset, $this->hotspot->asset->section, $this->hotspot->asset->entry] :
            [$this->hotspot, $this->hotspot->asset, $this->hotspot->asset->entry];
    }

    /**
     * @return string
     */
    public function getTrailModelType(): string
    {
        return Yii::t('hotspot', 'Hotspot Asset');
    }

    /**
     * @return HotspotAssetActiveForm|Widget
     */
    public function getActiveForm()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return static::getTypes()[$this->type]['activeForm'] ?? HotspotAssetActiveForm::class;
    }

    /**
     * @return string
     */
    public function getParentGridView(): string
    {
        return HotspotAssetParentGridView::class;
    }

    /**
     * @return string
     */
    public function getParentName(): string
    {
        return Yii::t('hotspot', 'Hotspots');
    }

    /**
     * @return string
     */
    public function getFileCountAttribute(): string
    {
        return 'hotspot_asset_count';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'section_id' => Yii::t('cms', 'Section'),
            'file_id' => Yii::t('media', 'File'),
            'alt_text' => Yii::t('cms', 'Alt text'),
            'link' => Yii::t('cms', 'Link'),
        ]);
    }

    /**
     * @return string
     */
    public function formName(): string
    {
        return 'HotspotAsset';
    }

    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('hotspot_asset');
    }
}