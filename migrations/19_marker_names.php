<?php

require_once(realpath(__DIR__.'/../models/GarudaMarker.php'));

class MarkerNames extends Migration
{
    public function up()
    {
        /*
         * Add new column to markers table for specifying a custom sorting
         * position field.
         */
        DBManager::get()->exec("ALTER TABLE `garuda_markers` ADD `name` VARCHAR(255)
            NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci AFTER `marker`;");

        $names = array(
            'DEARSIRMADAM' => 'Anrede (englisch)',
            'FIRSTNAME' => 'Vorname',
            'FULLNAME' => 'Voller Name',
            'LASTNAME' => 'Nachname',
            'SEHRGEEHRTE' => 'Anrede',
            'TOKEN' => 'Personalisierter Code o.ä.',
            'USERNAME' => 'Nutzername'
        );

        $stmt = DBManager::get()->prepare("UPDATE `garuda_markers` SET `name` = :name WHERE `marker` = :marker");

        foreach ($names as $marker => $name) {
            $stmt->execute(array('name' => $name, 'marker' => $marker));
        }

        // All entries updated, now add unique constraint for name.
        DBManager::get()->exec("ALTER TABLE `garuda_markers` ADD UNIQUE `name` (`name`)");

        GarudaMarker::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_markers` DROP `name`");

        GarudaMarker::expireTableScheme();
    }
}
