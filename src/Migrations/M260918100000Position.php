<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Hirtz\Cms\Hotspot\Models\Hotspot;
use yii\db\Migration;

/**
 * `Hotspot::beforeSave()` assigned the position with `??=` while the column defaults to `0`, so every hotspot an
 * installation holds carries the same one. They are numbered per asset here, in the order they were placed.
 *
 * @noinspection PhpUnused
 */
class M260918100000Position extends Migration
{
    public function safeUp(): void
    {
        $table = Hotspot::tableName();

        $this->execute("
            UPDATE $table [[hotspot]]
            JOIN (
                SELECT [[id]], ROW_NUMBER() OVER (PARTITION BY [[asset_id]] ORDER BY [[position]], [[id]]) [[number]]
                FROM $table
            ) [[numbered]] ON [[numbered]].[[id]] = [[hotspot]].[[id]]
            SET [[hotspot]].[[position]] = [[numbered]].[[number]]
        ");
    }

    public function safeDown(): bool
    {
        // A position of `0` is what this migration exists to remove, so it is not restored.
        return false;
    }
}
