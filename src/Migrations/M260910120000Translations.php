<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\Translation;
use yii\db\Migration;

/**
 * Moves the translated attributes of the hotspot models from their `_xx` columns into {@see Translation} records.
 *
 * The hotspot asset is not among them: its model is gone and `M260912120000Assets` reads whichever of the two
 * shapes it finds on `hotspot_asset`.
 *
 * @noinspection PhpUnused
 */
class M260910120000Translations extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        foreach ($this->getModels() as $model) {
            $this->moveI18nColumnsToTranslations($model);
        }
    }

    public function safeDown(): void
    {
        foreach ($this->getModels() as $model) {
            $this->restoreI18nColumnsFromTranslations($model);
        }
    }

    /**
     * @return list<Hotspot>
     */
    protected function getModels(): array
    {
        return [
            Hotspot::create(),
        ];
    }
}
