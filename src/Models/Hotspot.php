<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Models;

use Closure;
use Hirtz\Cms\Hotspot\Models\Queries\HotspotQuery;
use Hirtz\Cms\Hotspot\Models\Types\HotspotType;
use Hirtz\Cms\Hotspot\Modules\ModuleTrait;
use Hirtz\Cms\Models\BlockAsset;
use Hirtz\Cms\Models\EntryAsset;
use Hirtz\Cms\Models\SectionAsset;
use Hirtz\Media\Models\Asset;
use Hirtz\Media\Models\Interfaces\AssetModelInterface;
use Hirtz\Media\Models\Queries\AssetQuery;
use Hirtz\Media\Models\Traits\AssetModelTrait;
use Hirtz\Skeleton\Behaviors\BlameableBehavior;
use Hirtz\Skeleton\Behaviors\TimestampBehavior;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\CustomAttributes\CustomAttribute;
use Hirtz\Skeleton\Models\CustomAttributes\HtmlCustomAttribute;
use Hirtz\Skeleton\Models\CustomAttributes\TextCustomAttribute;
use Hirtz\Skeleton\Models\CustomAttributes\UrlCustomAttribute;
use Hirtz\Skeleton\Models\Interfaces\CustomAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\DraftStatusAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\SearchableInterface;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Interfaces\TranslationInterface;
use Hirtz\Skeleton\Models\Interfaces\TypeAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\VisibleAttributeInterface;
use Hirtz\Skeleton\Models\Traits\AdminModelTrait;
use Hirtz\Skeleton\Models\Traits\CustomAttributesTrait;
use Hirtz\Skeleton\Models\Traits\DraftStatusAttributeTrait;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\Traits\SearchableTrait;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Hirtz\Skeleton\Models\Traits\TranslatableAttributesTrait;
use Hirtz\Skeleton\Models\Traits\TranslationTrait;
use Hirtz\Skeleton\Models\Traits\TypeAttributeTrait;
use Hirtz\Skeleton\Models\Traits\UpdatedByUserTrait;
use Hirtz\Skeleton\Models\Traits\VisibleAttributeTrait;
use Hirtz\Skeleton\Validators\DynamicRangeValidator;
use Hirtz\Skeleton\Validators\RelationValidator;
use Hirtz\Skeleton\Web\User as WebUser;
use Override;
use Yii;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;

/**
 * @property int $id
 * @property int $asset_id
 * @property string|null $name
 * @property string|null $content
 * @property string|null $link
 * @property string|float $x
 * @property string|float $y
 * @property int $position
 * @property int $asset_count
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property-read BlockAsset|EntryAsset|SectionAsset $asset {@see static::getAsset()}
 * @property-read HotspotAsset[] $assets {@see static::getAssets()}
 *
 * @mixin TrailBehavior
 */
class Hotspot extends ActiveRecord implements
    AssetModelInterface,
    CustomAttributeInterface,
    DraftStatusAttributeInterface,
    SearchableInterface,
    TrailModelInterface,
    TranslationInterface,
    TypeAttributeInterface,
    VisibleAttributeInterface
{
    use AdminModelTrait;
    use AssetModelTrait;
    use CustomAttributesTrait {
        getCustomAttributes as getOwnCustomAttributes;
    }
    use I18nAttributesTrait;
    use SearchableTrait;
    use TranslationTrait;
    use ModuleTrait;
    use DraftStatusAttributeTrait;
    use TrailModelTrait;
    use TranslatableAttributesTrait;
    use TypeAttributeTrait;
    use UpdatedByUserTrait;
    use VisibleAttributeTrait;

    public ?bool $shouldUpdateAssetAfterInsert = null;

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ];
    }

    #[Override]
    public function rules(): array
    {
        return [
            ...parent::rules(),
            [
                ['status', 'type'],
                DynamicRangeValidator::class,
                'skipOnEmpty' => false,
            ],
            [
                ['asset_id'],
                'required',
            ],
            [
                ['asset_id', 'position'],
                'integer',
            ],
            [
                ['asset_id'],
                RelationValidator::class,
                'required' => true,
            ],
            [
                ['x', 'y'],
                'required',
            ],
            [
                ['x', 'y'],
                'number',
                'max' => 100,
                'min' => 0,
            ],
        ];
    }

    /**
     * @return array<int|string, string|Closure(self): mixed>
     */
    #[Override]
    public function fields(): array
    {
        return [
            'id',
            'displayName' => fn (self $hotspot): string => $hotspot->getAdminName(),
            'x',
            'y',
            'url' => fn (self $hotspot): ?string => ($route = $hotspot->getAdminRoute())
                ? Yii::$app->getUrlManager()->createUrl($route)
                : null,
        ];
    }

    #[Override]
    public function beforeValidate(): bool
    {
        $this->status ??= static::STATUS_DEFAULT;
        $this->type ??= static::TYPE_DEFAULT;

        return parent::beforeValidate();
    }

    #[Override]
    public function afterValidate(): void
    {
        // Disable hotspot move / clone for now ...
        if (!$this->getIsNewRecord() && $this->isAttributeChanged('asset_id')) {
            $this->addInvalidAttributeError('asset_id');
        }

        parent::afterValidate();
    }

    #[Override]
    public function beforeSave($insert): bool
    {
        $this->attachBehaviors([
            'BlameableBehavior' => BlameableBehavior::class,
            'TimestampBehavior' => TimestampBehavior::class,
        ]);

        // The column defaults to `0`, so `??=` never fired and no hotspot was ever given a position;
        // `Migrations\M260918100000Position` renumbers the ones an installation already holds.
        $this->position = $this->position ?: ($this->getMaxPosition() + 1);

        $this->shouldUpdateAssetAfterInsert ??= !$this->getIsBatch();

        return parent::beforeSave($insert);
    }

    /**
     * @param array<string, mixed> $changedAttributes
     */
    #[Override]
    public function afterSave($insert, $changedAttributes): void
    {
        if ($insert) {
            if ($this->shouldUpdateAssetAfterInsert) {
                $this->updateAssetHotspotCount();
            }
        } elseif ($changedAttributes) {
            $this->asset->updated_at = $this->updated_at;
            $this->asset->update();
        }

        parent::afterSave($insert, $changedAttributes);
    }

    #[Override]
    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        if ($this->asset_count) {
            foreach ($this->assets as $asset) {
                $asset->delete();
            }
        }

        return true;
    }

    #[Override]
    public function afterDelete(): void
    {
        if (!$this->asset->isDeleted()) {
            $this->updateAssetHotspotCount();
        }

        parent::afterDelete();
    }

    /**
     * @return AssetQuery<Asset>
     */
    public function getAsset(): AssetQuery
    {
        /** @var AssetQuery<Asset> $relation */
        $relation = $this->hasOne(Asset::class, ['id' => 'asset_id']);
        return $relation;
    }

    /**
     * Resolved for every loaded record, with no relation populated, so nothing here may read one.
     *
     * @return list<CustomAttribute>
     */
    public function getCustomAttributes(): array
    {
        return [
            TextCustomAttribute::make('name')
                ->max(250)
                ->label(Yii::t('cms', 'HOTSPOT_NAME_LABEL'))
                ->translatable($this->isTranslatableAttribute('name')),
            HtmlCustomAttribute::make('content')
                ->label(Yii::t('cms', 'HOTSPOT_CONTENT_LABEL'))
                ->translatable($this->isTranslatableAttribute('content')),
            UrlCustomAttribute::make('link')
                ->label(Yii::t('cms', 'HOTSPOT_LINK_LABEL'))
                ->translatable($this->isTranslatableAttribute('link')),
            ...$this->getOwnCustomAttributes(),
        ];
    }

    #[Override]
    public static function find(): HotspotQuery
    {
        return Yii::createObject(HotspotQuery::class, [static::class]);
    }

    public function findSiblings(): HotspotQuery
    {
        return static::find()->where(['asset_id' => $this->asset_id]);
    }

    public function populateAssetRelation(?Asset $asset): void
    {
        $this->populateRelation('asset', $asset);
        $this->asset_id = $asset?->id;
    }

    public function updateAssetHotspotCount(): void
    {
        $this->asset->setAttribute('hotspot_count', (int)static::findSiblings()->count());
        $this->asset->update();
    }

    public function getMaxPosition(): int
    {
        return (int)$this->findSiblings()->max('[[position]]');
    }

    /**
     * @return list<string>
     */
    public function getTrailAttributes(): array
    {
        return array_values(array_diff($this->attributes(), [
            $this->getCustomAttributesColumn(),
            'position',
            'asset_count',
            'updated_by_user_id',
            'updated_at',
            'created_at',
        ]));
    }

    /**
     * @return list<TrailModelInterface>
     */
    public function getTrailParents(): array
    {
        $asset = $this->asset;

        return [$asset, ...$asset->getTrailParents()];
    }

    public function getAdminType(): string
    {
        return Yii::t('hotspot', 'COMMON_HOTSPOT');
    }

    public function getSearchAttributes(): array
    {
        return ['name', 'content'];
    }

    public function getSearchWeight(): float
    {
        return 0.4;
    }

    public static function findSearchable(): HotspotQuery
    {
        return static::find()->with('asset');
    }

    protected function getSearchResultTitle(): string
    {
        return implode(' › ', array_filter([$this->asset->getAdminName(), $this->getSearchTitle()]));
    }

    protected function isSearchResultVisible(): bool
    {
        return WebUser::current()?->can($this->getPermissionName()) ?? false;
    }

    #[Override]
    public static function getTypeClass(): string
    {
        return HotspotType::class;
    }

    #[Override]
    public function getType(): ?HotspotType
    {
        /** @var HotspotType|null */
        return static::findType(static::normalizeTypeValue($this->type ?? null));
    }

    public function getAdminRoute(): array|false
    {
        return $this->id ? ['/admin/hotspot/hotspot/update', 'id' => $this->id] : false;
    }

    /**
     * A hotspot is placed and ordered by its position on the asset, so that is what identifies it where it has
     * no name of its own — the primary key says nothing to whoever is placing them.
     */
    public function getAdminName(): string
    {
        return $this->getAdminNameAttributeValue() ?: $this->getAdminPositionLabel();
    }

    public function getAdminSubtitle(): string
    {
        return $this->getAdminPositionLabel();
    }

    /**
     * A hotspot is listed on the asset's own page rather than in an index of its own, so it has no listing crumb.
     */
    public function getAdminParent(): Asset
    {
        return $this->asset;
    }

    public function getPermissionName(): string
    {
        return $this->asset->getPermissionName();
    }

    public function getHtmlId(): string
    {
        return 'hotspot-' . $this->id;
    }

    /**
     * @return list<HotspotAsset>
     */
    public function getVisibleAssets(): array
    {
        return $this->allowsAssets() ? array_values($this->assets) : [];
    }

    public function getAssetClass(): string
    {
        return HotspotAsset::class;
    }

    public function allowsAssets(): bool
    {
        return static::getModule()->enableHotspotAssets && $this->typeAllowsAssets();
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'asset_id' => Yii::t('cms', 'HOTSPOT_ASSET_ID_LABEL'),
            'x' => Yii::t('hotspot', 'HOTSPOT_X_LABEL'),
            'y' => Yii::t('hotspot', 'HOTSPOT_Y_LABEL'),
            'asset_count' => Yii::t('media', 'MODEL_ASSET_COUNT_LABEL'),
        ];
    }

    #[Override]
    public function formName(): string
    {
        return 'Hotspot';
    }

    public function getTranslationModelClass(): string
    {
        return self::class;
    }

    #[Override]
    public static function tableName(): string
    {
        return '{{%hotspot}}';
    }
}
