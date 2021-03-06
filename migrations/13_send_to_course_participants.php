<?php

require_once(realpath(__DIR__.'/../models/GarudaMessage.php'));
require_once(realpath(__DIR__.'/../models/GarudaTemplate.php'));

class SendToCourseParticipants extends Migration
{
    public function up()
    {
        // Create new table for linking courses as message target.
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_courses` (
            `message_id` CHAR(32) NOT NULL COLLATE latin1_bin,
            `course_id` CHAR(32) NOT NULL COLLATE latin1_bin,
            PRIMARY KEY (`message_id`, `course_id`)
        )");

        // Add new entry 'courses' to possible message targets.
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `target`
            `target` ENUM ('all', 'students', 'employees', 'courses', 'usernames') NULL DEFAULT NULL");
        DBManager::get()->exec("ALTER TABLE `garuda_templates` CHANGE `target`
            `target` ENUM ('all', 'students', 'employees', 'courses', 'usernames') NULL DEFAULT NULL");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_courses`");

        // Remove entry 'courses' from possible message targets.
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `target`
            `target` ENUM ('all', 'students', 'employees', 'usernames') NULL DEFAULT NULL");

        SimpleORMap::expireTableScheme();
    }
}
