<?php

require_once(realpath(__DIR__.'/../models/GarudaFilter.php'));

class MessageTemplates extends Migration
{
    public function up()
    {
        // Create new table for message templates.
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_templates` (
            `template_id` INT NOT NULL AUTO_INCREMENT COLLATE latin1_bin,
            `name` VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci,
            `sender_id` CHAR(32) NOT NULL COLLATE latin1_bin REFERENCES `auth_user_md5`.`user_id`,
            `author_id` CHAR(32) NOT NULL COLLATE latin1_bin,
            `target` ENUM ('all', 'students', 'employees', 'usernames') DEFAULT 'all',
            `recipients` LONGTEXT NULL DEFAULT NULL COLLATE utf8mb4_unicode_ci,
            `subject` VARCHAR(255) NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci,
            `message` TEXT NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci,
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`template_id`)
        )");

        /*
         * Add new column to filters table for identifying whether a filter
         * belongs to a message or a template.
         */
        DBManager::get()->exec("ALTER TABLE `garuda_filters`
          ADD `type` ENUM ('message', 'template') NOT NULL DEFAULT 'message' AFTER `filter_id`");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_templates`");
        DBManager::get()->exec("ALTER TABLE `garuda_filters` DROP `type`");

        SimpleORMap::expireTableScheme();
    }
}
