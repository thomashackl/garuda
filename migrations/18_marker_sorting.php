<?php

require_once(realpath(__DIR__.'/../models/GarudaMarker.php'));

class MarkerSorting extends Migration
{
    public function up()
    {
        /*
         * Add new column to markers table for specifying a custom sorting
         * position field.
         */
        DBManager::get()->exec("ALTER TABLE `garuda_markers` ADD `position`
            TINYINT(1)
            NOT NULL DEFAULT 0 AFTER `replacement_unknown`;");

        $positions = array(
            'DEARSIRMADAM' => 6,
            'FIRSTNAME' => 3,
            'FULLNAME' => 2,
            'LASTNAME' => 4,
            'SEHRGEEHRTE' => 1,
            'TOKEN' => 7,
            'USERNAME' => 5
        );

        $stmt = DBManager::get()->prepare("UPDATE `garuda_markers` SET `position` = :pos WHERE `marker` = :marker");

        foreach ($positions as $marker => $pos) {
            $stmt->execute(array('pos' => $pos, 'marker' => $marker));
        }

        GarudaMarker::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_markers` DROP `position`");
        GarudaMarker::expireTableScheme();
    }
}
