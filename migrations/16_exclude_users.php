<?php

require_once(realpath(__DIR__.'/../models/GarudaMessage.php'));
require_once(realpath(__DIR__.'/../models/GarudaTemplate.php'));

class ExcludeUsers extends Migration
{
    public function up()
    {
        /*
         * Add new column to filters table for specifying excluded users.
         */
        DBManager::get()->exec("ALTER TABLE `garuda_messages`
          ADD `exclude_users` LONGTEXT NULL DEFAULT NULL AFTER `recipients`");
        DBManager::get()->exec("ALTER TABLE `garuda_templates`
          ADD `exclude_users` LONGTEXT NULL DEFAULT NULL AFTER `recipients`");
        GarudaMessage::expireTableScheme();
        GarudaTemplate::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `exclude_users`");
        DBManager::get()->exec("ALTER TABLE `garuda_templates` DROP `exclude_users`");
        GarudaMessage::expireTableScheme();
        GarudaTemplate::expireTableScheme();
    }
}
