<?php

namespace davidhirtz\yii2\hotspot\models\base;

use davidhirtz\yii2\hotspot\models\queries\HotspotAssetQuery;
use davidhirtz\yii2\hotspot\models\queries\HotspotQuery;
use davidhirtz\yii2\hotspot\modules\admin\Module;
use davidhirtz\yii2\hotspot\modules\admin\widgets\forms\HotspotActiveForm;
use davidhirtz\yii2\cms\modules\ModuleTrait;
use davidhirtz\yii2\cms\models\Asset;
use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\media\models\AssetParentInterface;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\db\I18nAttributesTrait;
use davidhirtz\yii2\skeleton\db\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\db\TypeAttributeTrait;
use davidhirtz\yii2\skeleton\models\queries\UserQuery;
use davidhirtz\yii2\skeleton\models\Trail;
use davidhirtz\yii2\skeleton\models\User;
use Yii;

/**
 * Class Hotspot
 * @package davidhirtz\yii2\hotspot\models\base
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
 * @property \davidhirtz\yii2\hotspot\models\HotspotAsset[] $assets
 * @method static \davidhirtz\yii2\hotspot\models\Hotspot findOne($condition)
 * @method static \davidhirtz\yii2\hotspot\models\Hotspot[] findAll($condition)
 */
class Hotspot extends ActiveRecord implements AssetParentInterface
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
                ['asset_id', 'position'],
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
     * @return string[]
     */
    public function fields()
    {
        return [
            'id',
            'displayName',
            'x',
            'y',
            'url' => function (self $hotspot) {
                return Yii::$app->getUrlManager()->createUrl($hotspot->getAdminRoute());
            },
        ];
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
        // Disable hotspot move / clone for now ...
        if (!$this->getIsNewRecord() && $this->isAttributeChanged('asset_id')) {
            $this->addInvalidAttributeError('asset_id');
        }

        // Prevent unnecessary attribute updates
        $this->x = number_format($this->x, 2);
        $this->y = number_format($this->y, 2);

        parent::afterValidate();
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
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $this->recalculateAssetHotspotCount();
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
            foreach ($this->assets as $asset) {
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
            $this->recalculateAssetHotspotCount();
            $this->asset->update();
        }

        parent::afterDelete();
    }

    /**
     * @return HotspotAssetQuery
     */
    public function getAsset()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasOne(Asset::class, ['id' => 'asset_id']);
    }

    /**
     * @return HotspotAssetQuery
     */
    public function getAssets()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->hasMany(\davidhirtz\yii2\hotspot\models\HotspotAsset::class, ['hotspot_id' => 'id'])
            ->orderBy(['position' => SORT_ASC])
            ->indexBy('id')
            ->inverseOf('hotspot');
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
     * @return HotspotQuery
     */
    public function findSiblings()
    {
        return static::find()->where(['asset_id' => $this->asset_id]);
    }

    /**
     * @return HotspotQuery
     */
    public static function find()
    {
        return Yii::createObject(HotspotQuery::class, [get_called_class()]);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function clone($attributes = [])
    {
        $clone = new \davidhirtz\yii2\hotspot\models\Hotspot();
        $clone->setAttributes(array_merge($this->getAttributes(), $attributes));

        if ($clone->insert()) {
            if ($this->asset_count) {
                /** @var HotspotAsset[] $assets */
                $assets = $this->getAssets()->all();

                foreach ($assets as $asset) {
                    $asset->clone(['hotspot_id' => $clone->id]);
                }
            }
        }

        return $clone;
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
     * Recalculates the asset's hotspot count.
     */
    protected function recalculateAssetHotspotCount()
    {
        $this->asset->setAttribute('hotspot_count', (int)static::findSiblings()->count());
    }

    /**
     * @param array $assetIds
     */
    public function updateAssetOrder($assetIds)
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

    /**
     * @return int
     */
    public function getMaxPosition(): int
    {
        return (int)$this->findSiblings()->max('[[position]]');
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
        return $this->asset->isSectionAsset() ? [$this->asset, $this->asset->section, $this->asset->entry] : [$this->asset, $this->asset->entry];
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
        return Yii::t('hotspot', 'Hotspot');
    }

    /**
     * @return array|false
     */
    public function getTrailModelAdminRoute()
    {
        return $this->getAdminRoute();
    }

    /**
     * @return HotspotActiveForm
     */
    public function getActiveForm()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return static::getTypes()[$this->type]['activeForm'] ?? HotspotActiveForm::class;
    }

    /**
     * @return array|false
     */
    public function getAdminRoute()
    {
        return $this->id ? ['/admin/hotspot/update', 'id' => $this->id] : false;
    }

    /**
     * @return mixed|string
     */
    public function getDisplayName()
    {
        return $this->getI18nAttribute('name') ?: Yii::t('cms', '[ No title ]');
    }

    /**
     * @return mixed|string
     */
    public function getHtmlId()
    {
        return $this->getI18nAttribute('slug') ?: ('hotspot-' . $this->id);
    }

    /**
     * @return bool
     */
    public function hasAssetsEnabled(): bool
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('hotspot');
        return $module->enableHotspotAssets;
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
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

    /**
     * @return string
     */
    public function formName()
    {
        return 'Hotspot';
    }

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return static::getModule()->getTableName('hotspot');
    }
}