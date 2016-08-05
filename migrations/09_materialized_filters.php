<?php

require_once(realpath(__DIR__.'/../models/GarudaMessage.php'));

class MaterializedFilters extends Migration
{
    function up()
    {
        // Create new table for referencing stored user filters.
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_filters` (
            `message_id` INT NOT NULL,
            `filter_id` CHAR(32) NOT NULL REFERENCES `userfilter`.`filter_id`,
            `user_id` CHAR(32) NOT NULL REFERENCES `auth_user_md5`.`user_id`,
            `mkdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`message_id`, `filter_id`)
        )");

        // Add new column for target user groups instead of concrete recipients.
        DBManager::get()->exec("ALTER TABLE `garuda_messages`
          ADD `target` ENUM ('all', 'students', 'employees', 'usernames') NOT NULL AFTER `author_id`,
          CHANGE `recipients` `recipients` LONGTEXT NULL");
        GarudaMessage::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_filters`");
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `target`, CHANGE `recipients` `recipients` LONGTEXT NOT NULL");
        GarudaMessage::expireTableScheme();
    }
}
