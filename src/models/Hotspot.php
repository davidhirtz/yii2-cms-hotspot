<?php

declare(strict_types=1);

namespace Hirtz\Cms\hotspot\Models;

use Hirtz\Cms\hotspot\Models\Queries\HotspotAssetQuery;
use Hirtz\Cms\hotspot\Models\Queries\HotspotQuery;
use Hirtz\Cms\hotspot\Modules\Admin\Module;
use Hirtz\Cms\Models\Asset;
use Hirtz\Cms\Models\Queries\AssetQuery;
use Hirtz\Cms\Models\Traits\VisibleAttributeTrait;
use Hirtz\Cms\modules\ModuleTrait;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use Hirtz\Media\Models\interfaces\AssetParentInterface;
use Hirtz\Media\Models\Traits\AssetParentTrait;
use Hirtz\Skeleton\Behaviors\BlameableBehavior;
use Hirtz\Skeleton\Behaviors\TimestampBehavior;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\Interfaces\DraftStatusAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\TypeAttributeInterface;
use Hirtz\Skeleton\Models\Traits\DraftStatusAttributeTrait;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\Traits\TypeAttributeTrait;
use Hirtz\Skeleton\Models\Traits\UpdatedByUserTrait;
use Hirtz\Skeleton\Validators\DynamicRangeValidator;
use Hirtz\Skeleton\Validators\HtmlValidator;
use Hirtz\Skeleton\Validators\RelationValidator;
use Yii;

/**
 * @property int $id
 * @property int $status
 * @property int $type
 * @property int $asset_id
 * @property string $name
 * @property string $content
 * @property string $link
 * @property string|float $x
 * @property string|float $y
 * @property int $position
 * @property int $asset_count
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 *
 * @property-read Asset $asset {@see static::getAsset()}
 * @property-read HotspotAsset[] $assets {@see static::getAssets()}
 *
 * @mixin TrailBehavior
 */
class Hotspot extends ActiveRecord implements AssetParentInterface, DraftStatusAttributeInterface, TypeAttributeInterface
{
    use AssetParentTrait;
    use I18nAttributesTrait;
    use ModuleTrait;
    use DraftStatusAttributeTrait;
    use TypeAttributeTrait;
    use UpdatedByUserTrait;
    use VisibleAttributeTrait;

    /**
     * @var array|string|false used when `$contentType` is set to "html". use an array with the first value containing
     * a validator class, following keys can be used to configure the validator, string containing the class name or
     * false for disabling the validation.
     */
    public array|string|false $htmlValidator = HtmlValidator::class;

    /**
     * @var string|false the content type, "html" enables HTML validators and WYSIWYG editor
     */
    public string|false $contentType = 'html';

    public ?bool $shouldUpdateAssetAfterInsert = null;

    #[\Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ];
    }

    #[\Override]
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
                'filter',
                'filter' => 'intval',
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
            ...$this->getI18nRules([
                [
                    ['name', 'content', 'link'],
                    'trim',
                ],
                [
                    ['name', 'link'],
                    'string',
                    'max' => 250,
                ],
                [
                    $this->getI18nAttributesNames(['content']),
                    ...(array)($this->contentType == 'html' && $this->htmlValidator ? $this->htmlValidator : 'safe'),
                ],
            ]),
        ];
    }

    #[\Override]
    public function fields(): array
    {
        return [
            'id',
            'displayName',
            'x',
            'y',
            'url' => fn (self $hotspot) => Yii::$app->getUrlManager()->createUrl($hotspot->getAdminRoute()),
        ];
    }

    #[\Override]
    public function beforeValidate(): bool
    {
        $this->status ??= static::STATUS_DEFAULT;
        $this->type ??= static::TYPE_DEFAULT;

        return parent::beforeValidate();
    }

    #[\Override]
    public function afterValidate(): void
    {
        // Disable hotspot move / clone for now ...
        if (!$this->getIsNewRecord() && $this->isAttributeChanged('asset_id')) {
            $this->addInvalidAttributeError('asset_id');
        }

        parent::afterValidate();
    }

    #[\Override]
    public function beforeSave($insert): bool
    {
        $this->attachBehaviors([
            'BlameableBehavior' => BlameableBehavior::class,
            'TimestampBehavior' => TimestampBehavior::class,
        ]);

        // Sanitize values to prevent unnecessary attribute updates
        $this->x = number_format($this->x, 2);
        $this->y = number_format($this->y, 2);

        $this->position ??= $this->getMaxPosition() + 1;

        $this->shouldUpdateAssetAfterInsert ??= !$this->getIsBatch();

        return parent::beforeSave($insert);
    }

    #[\Override]
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

    #[\Override]
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

    #[\Override]
    public function afterDelete(): void
    {
        if (!$this->asset->isDeleted()) {
            $this->updateAssetHotspotCount();
        }

        parent::afterDelete();
    }

    public function getAsset(): AssetQuery
    {
        /** @var AssetQuery $relation */
        $relation = $this->hasOne(Asset::class, ['id' => 'asset_id']);
        return $relation;
    }

    public function getAssets(): HotspotAssetQuery
    {
        /** @var HotspotAssetQuery $relation */
        $relation = $this->hasMany(HotspotAsset::class, ['hotspot_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('hotspot');

        return $relation;
    }

    #[\Override]
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

    public function populateAssetRelations(?array $assets = null): void
    {
        $this->populateRelation('assets', $assets);
    }

    public function recalculateAssetCount(): static
    {
        $this->asset_count = $this->getAssets()->count();
        return $this;
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

    public function getTrailParents(): array
    {
        return $this->asset->isSectionAsset()
            ? [$this->asset, $this->asset->section, $this->asset->entry]
            : [$this->asset, $this->asset->entry];
    }

    public function getTrailModelName(): string
    {
        if ($this->id) {
            return Yii::t('skeleton', '{model} #{id}', [
                'model' => $this->getTrailModelType(),
                'id' => $this->id,
            ]);
        }

        return $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Yii::t('hotspot', 'Hotspot');
    }

    public function getTrailModelAdminRoute(): array|false
    {
        return $this->getAdminRoute();
    }

    public function getAdminRoute(): array|false
    {
        return $this->id ? ['/admin/hotspot/update', 'id' => $this->id] : false;
    }

    public function getDisplayName(): string
    {
        return $this->getI18nAttribute('name') ?: Yii::t('cms', '[ No title ]');
    }

    public function getHtmlId(): string
    {
        return $this->getI18nAttribute('slug') ?: ('hotspot-' . $this->id);
    }

    public function getVisibleAssets(): array
    {
        return $this->hasAssetsEnabled() && $this->isAttributeVisible('#assets') ? $this->assets : [];
    }

    public function hasAssetsEnabled(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');
        return $module->enableHotspotAssets;
    }

    #[\Override]
    public function attributeLabels(): array
    {
        return [...parent::attributeLabels(), 'asset_id' => Yii::t('cms', 'Asset'), 'name' => Yii::t('cms', 'Title'), 'content' => Yii::t('cms', 'Content'), 'link' => Yii::t('cms', 'Link'), 'x' => Yii::t('hotspot', 'Horizontal position'), 'y' => Yii::t('hotspot', 'Vertical position'), 'asset_count' => Yii::t('hotspot', 'Hotspot Asset')];
    }

    #[\Override]
    public function formName(): string
    {
        return 'Hotspot';
    }

    #[\Override]
    public static function tableName(): string
    {
        return static::getModule()->getTableName('hotspot');
    }
}
