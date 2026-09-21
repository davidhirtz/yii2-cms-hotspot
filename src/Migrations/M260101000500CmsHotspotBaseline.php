<?php

declare(strict_types=1);

namespace Hirtz\Cms\Hotspot\Migrations;

use Override;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260101000500CmsHotspotBaseline extends Migration
{
    #[Override]
    public function safeUp(): void
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE `hotspot` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `status` tinyint(3) unsigned NOT NULL DEFAULT 3,
              `type` smallint(6) unsigned NOT NULL DEFAULT 1,
              `asset_id` int(11) unsigned NOT NULL,
              `x` decimal(5,2) NOT NULL,
              `y` decimal(5,2) NOT NULL,
              `custom_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_attributes`)),
              `position` smallint(6) NOT NULL DEFAULT 0,
              `asset_count` smallint(6) NOT NULL DEFAULT 0,
              `updated_by_user_id` int(11) unsigned DEFAULT NULL,
              `updated_at` datetime DEFAULT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `asset_id` (`asset_id`,`position`),
              KEY `hotspot_updated_by_ibfk` (`updated_by_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `asset` ADD `hotspot_count` smallint(6) NOT NULL DEFAULT '0'
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `hotspot` ADD CONSTRAINT `hotspot_asset_id_ibfk` FOREIGN KEY (`asset_id`) REFERENCES `asset` (`id`) ON DELETE CASCADE
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `hotspot` ADD CONSTRAINT `hotspot_updated_by_ibfk` FOREIGN KEY (`updated_by_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
            SQL
        );
    }

    #[Override]
    public function safeDown(): bool
    {
        echo "    > a baseline cannot be reverted, restore a dump instead\n";
        return false;
    }
}
