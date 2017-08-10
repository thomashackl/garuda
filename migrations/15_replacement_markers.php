<?php

require_once(realpath(__DIR__.'/../models/GarudaMarker.php'));

class ReplacementMarkers extends Migration
{
    public function up()
    {
        // Create new table for replacement markers.
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_markers` (
            `marker_id` CHAR(32) NOT NULL COLLATE latin1_bin,
            `marker` VARCHAR(150) UNIQUE NOT NULL COLLATE utf8mb4_unicode_ci,
            `type` ENUM ('text', 'database', 'function', 'token') NOT NULL DEFAULT 'text',
            `description` TEXT NOT NULL COLLATE utf8mb4_unicode_ci,
            `replacement` TEXT NOT NULL COLLATE utf8mb4_unicode_ci,
            `replacement_female` TEXT NULL COLLATE utf8mb4_unicode_ci,
            `replacement_unknown` TEXT NULL COLLATE utf8mb4_unicode_ci,
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`marker_id`))
            ENGINE=InnoDB ROW_FORMAT=DYNAMIC
        ");

        // Fill with available entries.
        $markers = array(
            array(
                'marker' => 'FULLNAME',
                'type' => 'database',
                'description' => 'Hier wird der volle Name der jeweiligen Person eingesetzt, z.B. "Prof. Max Mustermann, PhD".',
                'replacement' => 'user_info.title_front ###FIRSTNAME### ###LASTNAME### user_info.title_rear',
            ),
            array(
                'marker' => 'FIRSTNAME',
                'type' => 'database',
                'description' => 'Hier wird der Vorname der jeweiligen Person eingesetzt.',
                'replacement' => 'auth_user_md5.Vorname',
            ),
            array(
                'marker' => 'LASTNAME',
                'type' => 'database',
                'description' => 'Hier wird der Nachname der jeweiligen Person eingesetzt.',
                'replacement' => 'auth_user_md5.Nachname',
            ),
            array(
                'marker' => 'USERNAME',
                'type' => 'database',
                'description' => 'Hier wird der Nutzername der jeweiligen Person eingesetzt.',
                'replacement' => 'auth_user_md5.username',
            ),
            array(
                'marker' => 'SEHRGEEHRTE',
                'type' => 'text',
                'description' => 'Hier wird eine Anrede erzeugt: "Sehr geehrte Michaela Musterfrau" bzw. "Sehr geehrter Max Mustermann".',
                'replacement' => 'Sehr geehrter ###FULLNAME###',
                'replacement_female' => 'Sehr geehrte ###FULLNAME###',
                'replacement_unknown' => 'Sehr geehrte/r ###FULLNAME###',
            ),
            array(
                'marker' => 'DEARSIRMADAM',
                'type' => 'text',
                'description' => 'Creates a Salutation: "Dear Jane Doe" or "Dear John Doe".',
                'replacement' => 'Dear ###FULLNAME###',
            ),
            array(
                'marker' => 'TOKEN',
                'type' => 'token',
                'description' => 'Hier wird ein persönlicher Teilnahmecode o.ä. aus einer hochgeladenen Datei eingesetzt.',
                'replacement' => 'garuda_tokens.token'
            ),
        );

        foreach ($markers as $data) {
            GarudaMarker::create($data);
        }

    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_markers`");
    }
}
