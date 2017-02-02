<?php

require_once(realpath(__DIR__.'/../models/GarudaMarker.php'));

class MarkerPermissions extends Migration
{
    public function up()
    {
        /*
         * Add new column to markers table for specifying a permission level
         * for marker usage.
         */
        DBManager::get()->exec("ALTER TABLE `garuda_markers` ADD `permission`
            ENUM('root','admin','dozent','tutor')
            NOT NULL DEFAULT 'root' AFTER `description`;");
        // Tokens may only be used by roots.
        DBManager::get()->exec("UPDATE `garuda_markers` SET `permission` = 'root' WHERE `marker` = 'TOKEN'");
        // All other markers are available for everyone.
        DBManager::get()->exec("UPDATE `garuda_markers` SET `permission` = 'tutor' WHERE `marker` != 'TOKEN'");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_markers` DROP `permission`");

        SimpleORMap::expireTableScheme();
    }
}
