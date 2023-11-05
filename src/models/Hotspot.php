<?php

namespace davidhirtz\yii2\cms\hotspot\models;

use davidhirtz\yii2\cms\hotspot\models\queries\HotspotAssetQuery;
use davidhirtz\yii2\cms\hotspot\models\queries\HotspotQuery;
use davidhirtz\yii2\cms\hotspot\modules\admin\Module;
use davidhirtz\yii2\cms\models\queries\AssetQuery;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\media\models\interfaces\AssetParentInterface;
use davidhirtz\yii2\skeleton\behaviors\BlameableBehavior;
use davidhirtz\yii2\skeleton\behaviors\TimestampBehavior;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\db\TypeAttributeTrait;
use davidhirtz\yii2\skeleton\helpers\ArrayHelper;
use davidhirtz\yii2\skeleton\models\Trail;
use davidhirtz\yii2\skeleton\models\traits\UpdatedByUserTrait;
use davidhirtz\yii2\skeleton\validators\DynamicRangeValidator;
use davidhirtz\yii2\skeleton\validators\HtmlValidator;
use davidhirtz\yii2\skeleton\validators\RelationValidator;
use Yii;

/**
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
 *
 * @property-read Asset $asset {@see static::getAsset()}
 * @property-read HotspotAsset[] $assets {@see static::getAssets()}
 */
class Hotspot extends ActiveRecord implements \davidhirtz\yii2\media\models\interfaces\AssetParentInterface
{
    use I18nAttributesTrait;
    use ModuleTrait;
    use StatusAttributeTrait;
    use TypeAttributeTrait;
    use UpdatedByUserTrait;

    /**
     * @var array|string|false used when $contentType is set to "html". use an array with the first value containing a
     * validator class, following keys can be used to configure the validator, string containing the class
     * name or false for disabling the validation.
     */
    public array|string|false $htmlValidator = HtmlValidator::class;

    /**
     * @var string|false the content type, "html" enables html validators and WYSIWYG editor
     */
    public string|false $contentType = 'html';

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ]);
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), $this->getI18nRules([
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
            [
                ['name', 'content', 'link'],
                'trim',
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

    public function fields(): array
    {
        return [
            'id',
            'displayName',
            'x',
            'y',
            'url' => fn(self $hotspot) => Yii::$app->getUrlManager()->createUrl($hotspot->getAdminRoute()),
        ];
    }

    public function beforeValidate(): bool
    {
        $this->status ??= static::STATUS_DEFAULT;
        $this->type ??= static::TYPE_DEFAULT;

        return parent::beforeValidate();
    }

    public function afterValidate(): void
    {
        // Disable hotspot move / clone for now ...
        if (!$this->getIsNewRecord() && $this->isAttributeChanged('asset_id')) {
            $this->addInvalidAttributeError('asset_id');
        }

        // Prevent unnecessary attribute updates
        $this->x = number_format($this->x, 2);
        $this->y = number_format($this->y, 2);

        parent::afterValidate();
    }

    public function beforeSave($insert): bool
    {
        $this->attachBehaviors([
            'BlameableBehavior' => BlameableBehavior::class,
            'TimestampBehavior' => TimestampBehavior::class,
        ]);

        $this->position ??= $this->getMaxPosition() + 1;

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes): void
    {
        if ($insert) {
            $this->recalculateAssetHotspotCount();
            $this->asset->update();
        }

        parent::afterSave($insert, $changedAttributes);
    }

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

    public function afterDelete(): void
    {
        if (!$this->asset->isDeleted()) {
            $this->recalculateAssetHotspotCount();
            $this->asset->update();
        }

        parent::afterDelete();
    }

    public function getAsset(): AssetQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    public function getAssets(): ActiveQuery
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasMany(HotspotAsset::class, ['hotspot_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('hotspot');
    }

    public static function find(): HotspotQuery
    {
        return Yii::createObject(HotspotQuery::class, [get_called_class()]);
    }

    public function findSiblings(): HotspotQuery
    {
        return static::find()->where(['asset_id' => $this->asset_id]);
    }

    public function clone(array $attributes = []): static
    {
        $asset = ArrayHelper::remove($attributes, 'asset');

        $clone = new static();
        $clone->setAttributes(array_merge($this->getAttributes($this->safeAttributes()), $attributes));

        if ($asset) {
            $clone->populateAssetRelation($asset);
        }

        if ($clone->insert()) {
            if ($this->asset_count) {
                $assets = $this->getAssets()->all();

                foreach ($assets as $asset) {
                    $asset->clone(['hotspot' => $clone]);
                }
            }
        }

        return $clone;
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

    protected function recalculateAssetHotspotCount(): void
    {
        $this->asset->setAttribute('hotspot_count', (int)static::findSiblings()->count());
    }

    public function updateAssetOrder(array $assetIds): void
    {
        $assets = $this->getAssets()
            ->select(['id', 'position'])
            ->andWhere(['id' => $assetIds])
            ->all();

        if (HotspotAsset::updatePosition($assets, array_flip($assetIds))) {
            $trail = Trail::createOrderTrail($this, Yii::t('hotspot', 'Hotspot asset order changed'));

            foreach ($this->getTrailParents() as $model) {
                Trail::createOrderTrail($model, Yii::t('hotspot', 'Hotspot asset order changed'), [
                    'trail_id' => $trail->id,
                ]);
            }

            $this->updated_at = new DateTime();
            $this->update();
        }
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

    /** @noinspection PhpUnused */
    public function getHtmlId(): string
    {
        return $this->getI18nAttribute('slug') ?: ('hotspot-' . $this->id);
    }

    public function hasAssetsEnabled(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');
        return $module->enableHotspotAssets;
    }


    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'asset_id' => Yii::t('cms', 'Asset'),
            'name' => Yii::t('cms', 'Title'),
            'content' => Yii::t('cms', 'Content'),
            'link' => Yii::t('cms', 'Link'),
            'x' => Yii::t('hotspot', 'Horizontal position'),
            'y' => Yii::t('hotspot', 'Vertical position'),
            'asset_count' => Yii::t('hotspot', 'Hotspot Asset'),
        ]);
    }

    public function formName(): string
    {
        return 'Hotspot';
    }

    public static function tableName(): string
    {
        return static::getModule()->getTableName('hotspot');
    }
}