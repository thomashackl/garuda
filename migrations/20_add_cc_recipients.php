<?php

require_once(realpath(__DIR__ . '/../models/GarudaMessage.php'));
require_once(realpath(__DIR__ . '/../models/GarudaTemplate.php'));

class AddCCRecipients extends Migration
{
    public function up()
    {
        /* Cleanup "old" migration 20 which was a failed experiment */
        // Remove cronjob manually as class isn't there anymore.
        $task = DBManager::get()->fetchColumn(
            "SELECT `task_id` FROM `cronjobs_tasks` WHERE `class` = 'GarudaGetPermissionsCronjob'");
        if ($task !== '') {
            DBManager::get()->execute("DELETE FROM `cronjobs_schedules` WHERE `task_id` = ?", [$task]);
            DBManager::get()->execute("DELETE FROM `cronjobs_tasks` WHERE `task_id` = ?", [$task]);
        }
        // Delete config entries.
        Config::get()->delete('GARUDA_PERMISSIONS_EXTERNAL_DB_INSTITUTES');
        Config::get()->delete('GARUDA_PERMISSIONS_EXTERNAL_DB_SETTINGS');
        Config::get()->delete('GARUDA_PERMISSIONS_EXTERNAL_DB_ENABLE');
        /* End cleanup */

        /* Add database field for recipients in CC */
        DBManager::get()->execute(
            "ALTER TABLE `garuda_messages` ADD `cc` TEXT NULL DEFAULT NULL AFTER `exclude_users`");
        DBManager::get()->execute(
            "ALTER TABLE `garuda_templates` ADD `cc` TEXT NULL DEFAULT NULL AFTER `exclude_users`");

        GarudaMessage::expireTableScheme();
        GarudaTemplate::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `garuda_messages` DROP `cc`");
        DBManager::get()->execute("ALTER TABLE `garuda_templates` DROP `cc`");

        GarudaMessage::expireTableScheme();
        GarudaTemplate::expireTableScheme();
    }
}
