<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Models;

use Hirtz\Cms\hotspot\Models\Queries\HotspotAssetQuery;
use Hirtz\Cms\hotspot\Models\Queries\HotspotQuery;
use Hirtz\Cms\hotspot\Modules\Admin\Widgets\Panels\HotspotAssetFilePanel;
use Hirtz\Cms\Models\ActiveRecord;
use Hirtz\Cms\Models\Traits\VisibleAttributeTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use Hirtz\Media\Models\interfaces\AssetInterface;
use Hirtz\Media\Models\Traits\AssetTrait;
use Hirtz\Media\Models\Traits\FileRelationTrait;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Validators\RelationValidator;
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
    use VisibleAttributeTrait;

    public ?bool $shouldUpdateHotspotAfterInsert = null;

    #[\Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => [
                'class' => TrailBehavior::class,
                'modelClass' => static::getModule()->getI18nClassName(static::class),
            ],
        ];
    }

    #[\Override]
    public function rules(): array
    {
        return [
            ...parent::rules(),
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
        ];
    }

    #[\Override]
    public function beforeSave($insert): bool
    {
        $this->shouldUpdateHotspotAfterInsert ??= !$this->getIsBatch();
        return parent::beforeSave($insert);
    }

    #[\Override]
    public function afterSave($insert, $changedAttributes): void
    {
        if ($insert) {
            if ($this->shouldUpdateHotspotAfterInsert) {
                $this->updateHotspotAssetCount();
            }

            $this->updateFileRelatedCount();
        } elseif ($changedAttributes) {
            $this->hotspot->updated_at = $this->updated_at;
            $this->hotspot->update();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    #[\Override]
    public function afterDelete(): void
    {
        if (!$this->hotspot->isDeleted()) {
            $this->updateHotspotAssetCount();
        }

        if (!$this->file->isDeleted()) {
            $this->updateFileRelatedCount();
        }

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

    #[\Override]
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

    public function updateFileRelatedCount(): bool|int
    {
        $attributeName = static::getModule()->enableI18nTables
            ? Yii::$app->getI18n()->getAttributeName('hotspot_asset_count')
            : 'hotspot_asset_count';

        $this->file->$attributeName = self::find()->where(['file_id' => $this->file_id])->count();
        return $this->file->update();
    }

    #[\Override]
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

    public function getFilePanelClass(): string
    {
        return HotspotAssetFilePanel::class;
    }

    public function getFileCountAttributeNames(): array
    {
        $languages = static::getModule()->getLanguages();
        $attributes = array_map(fn ($lang) => Yii::$app->getI18n()->getAttributeName('hotspot_asset_count', $lang), $languages);

        return array_combine($languages, $attributes);
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

    #[\Override]
    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'section_id' => Yii::t('cms', 'Section'),
            'file_id' => Yii::t('media', 'File'),
            'alt_text' => Yii::t('cms', 'Alt text'),
            'link' => Yii::t('cms', 'Link'),
        ];
    }

    #[\Override]
    public function formName(): string
    {
        return 'HotspotAsset';
    }

    #[\Override]
    public static function tableName(): string
    {
        return static::getModule()->getTableName('hotspot_asset');
    }
}
