<?php

namespace davidhirtz\yii2\cms\hotspot\models;

use davidhirtz\yii2\cms\models\ActiveRecord;
use davidhirtz\yii2\cms\hotspot\models\queries\HotspotAssetQuery;
use davidhirtz\yii2\cms\hotspot\models\queries\HotspotQuery;
use davidhirtz\yii2\cms\hotspot\modules\admin\widgets\grids\HotspotAssetParentGridView;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\media\models\interfaces\AssetInterface;
use davidhirtz\yii2\media\models\traits\AssetTrait;
use davidhirtz\yii2\media\models\traits\FileRelationTrait;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\validators\RelationValidator;
use Yii;

/**
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
 * @property-read Hotspot $hotspot {@see static::getHotspot()}
 * @property-read Hotspot $parent {@see static::getParent()}
 *
 * @mixin TrailBehavior
 */
class HotspotAsset extends ActiveRecord implements AssetInterface
{
    use AssetTrait;
    use FileRelationTrait;

    public ?bool $shouldUpdateHotspotAfterInsert = null;

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => [
                'class' => TrailBehavior::class,
                'modelClass' => static::getModule()->getI18nClassName(static::class),
            ],
        ]);
    }

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
            ],
            [
                ['file_id'],
                RelationValidator::class,
            ],
            [
                $this->getI18nAttributesNames(['name', 'alt_text', 'link']),
                'string',
                'max' => 250,
            ],
        ]);
    }

    public function beforeSave($insert): bool
    {
        $this->shouldUpdateHotspotAfterInsert ??= !$this->getIsBatch();
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes): void
    {
        if ($insert) {
            if ($this->shouldUpdateHotspotAfterInsert) {
                $this->updateHotspotAssetCount();
            }

            $this->updateOrDeleteFileByAssetCount();
        } elseif ($changedAttributes) {
            $this->hotspot->updated_at = $this->updated_at;
            $this->hotspot->update();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete(): void
    {
        if (!$this->hotspot->isDeleted()) {
            $this->updateHotspotAssetCount();
        }

        $this->updateOrDeleteFileByAssetCount();
        parent::afterDelete();
    }

    public function getHotspot(): HotspotQuery
    {
        /** @var HotspotQuery $relation */
        $relation = $this->hasOne(Hotspot::class, ['id' => 'hotspot_id']);
        return $relation;
    }

    public function findSiblings(): HotspotAssetQuery
    {
        return static::find()->where(['hotspot_id' => $this->hotspot_id]);
    }

    public static function find(): HotspotAssetQuery
    {
        return Yii::createObject(HotspotAssetQuery::class, [static::class]);
    }

    protected function updateHotspotAssetCount(): int
    {
        return $this->hotspot->recalculateAssetCount()->update();
    }

    public function populateHotspotRelation(?Hotspot $hotspot): void
    {
        $this->populateRelation('hotspot', $hotspot);
        $this->hotspot_id = $hotspot?->id;
    }

    public function getMaxPosition(): int
    {
        return (int)$this->findSiblings()->max('[[position]]');
    }

    public function getParent(): Hotspot
    {
        return $this->hotspot;
    }

    public function getRoute(): false|array
    {
        return false;
    }

    public function getAdminRoute(): false|array
    {
        return ['/admin/hotspot-asset/update', 'id' => $this->id];
    }

    public function getTrailParents(): array
    {
        return $this->hotspot->asset->isSectionAsset() ? [$this->hotspot, $this->hotspot->asset, $this->hotspot->asset->section, $this->hotspot->asset->entry] :
            [$this->hotspot, $this->hotspot->asset, $this->hotspot->asset->entry];
    }

    public function getTrailModelType(): string
    {
        return Yii::t('hotspot', 'Hotspot Asset');
    }

    /**
     * @return class-string
     */
    public function getParentGridView(): string
    {
        return HotspotAssetParentGridView::class;
    }

    public function getParentName(): string
    {
        return Yii::t('hotspot', 'Hotspots');
    }

    public function getFileCountAttribute(): string
    {
        return 'hotspot_asset_count';
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'section_id' => Yii::t('cms', 'Section'),
            'file_id' => Yii::t('media', 'File'),
            'alt_text' => Yii::t('cms', 'Alt text'),
            'link' => Yii::t('cms', 'Link'),
        ]);
    }

    public function formName(): string
    {
        return 'HotspotAsset';
    }

    public static function tableName(): string
    {
        return static::getModule()->getTableName('hotspot_asset');
    }
}