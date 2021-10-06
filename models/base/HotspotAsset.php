<?php

namespace davidhirtz\yii2\hotspot\models\base;

use davidhirtz\yii2\hotspot\models\queries\HotspotQuery;
use davidhirtz\yii2\hotspot\modules\admin\Module;
use davidhirtz\yii2\hotspot\modules\admin\widgets\forms\HotspotAssetActiveForm;
use davidhirtz\yii2\hotspot\modules\admin\widgets\grid\HotspotAssetParentGridView;
use davidhirtz\yii2\cms\modules\ModuleTrait;
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
 * Class HotspotAsset
 * @package davidhirtz\yii2\hotspot\models\base
 * @see \davidhirtz\yii2\hotspot\models\Asset
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
 * @property \davidhirtz\yii2\hotspot\models\Hotspot $hotspot
 * @property File $file
 * @property User $updated
 *
 * @method static \davidhirtz\yii2\hotspot\models\HotspotAsset findOne($condition)
 */
class HotspotAsset extends ActiveRecord implements AssetInterface
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
                'davidhirtz\yii2\skeleton\validators\RelationValidator',
                'relation' => 'hotspot',
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
        return static::find()->where(['hotspot_id' => $this->hotspot_id]);
    }

    /**
     * @return ActiveQuery
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
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
     * @return array
     */
    public function getTrailParents()
    {
        return [$this->hotspot];
    }

    /**
     * @return string
     */
    public function getTrailModelType(): string
    {
        return Yii::t('hotspot', 'Asset');
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
     * @return mixed|string
     */
    public function getParentName(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');
        return $module->name;
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
            'section_id' => Yii::t('hotspot', 'Section'),
            'file_id' => Yii::t('media', 'File'),
            'alt_text' => Yii::t('hotspot', 'Alt text'),
            'link' => Yii::t('hotspot', 'Link'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return static::getModule()->getTableName('hotspot_asset');
    }
}