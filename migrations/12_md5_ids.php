<?php

require_once(realpath(__DIR__.'/../models/GarudaMessage.php'));

class MD5IDs extends Migration
{
    public function up()
    {
        // Change datatype of ID columns.
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `job_id` `job_id` CHAR(32) NOT NULL");
        DBManager::get()->exec("ALTER TABLE `garuda_templates` CHANGE `template_id` `template_id` CHAR(32) NOT NULL");
        DBManager::get()->exec("ALTER TABLE `garuda_tokens` CHANGE `job_id` `job_id` CHAR(32) NOT NULL");
        DBManager::get()->exec("ALTER TABLE `garuda_filters` CHANGE `message_id` `message_id` CHAR(32) NOT NULL");

        // Generate new md5-IDs for all entries, first we get the messages to send.
        $messages = DBManager::get()->fetchAll("SELECT `job_id` FROM `garuda_messages`");
        foreach ($messages as $m) {
            // Generate new ID...
            do {
                $id = md5(uniqid('garuda_messages', 1));
                $db = DBManager::get()->query("SELECT `job_id` FROM `garuda_messages` "
                    . "WHERE `job_id` = '$id'");
            } while($db->fetch());
            // ... and update all relevant tables.
            DBManager::get()->execute("UPDATE `garuda_messages` SET `job_id` = :newid WHERE `job_id` = :oldid",
                array('newid' => $id, 'oldid' => $m['job_id']));
            DBManager::get()->execute("UPDATE `garuda_tokens` SET `job_id` = :newid WHERE `job_id` = :oldid",
                array('newid' => $id, 'oldid' => $m['job_id']));
            DBManager::get()->execute("UPDATE `garuda_filters` SET `message_id` = :newid
                WHERE `message_id` = :oldid AND `type` = 'message'",
                array('newid' => $id, 'oldid' => $m['job_id']));
        }

        // Now process the stored templates
        $messages = DBManager::get()->fetchAll("SELECT `template_id` FROM `garuda_templates`");
        foreach ($messages as $m) {
            // Generate new ID...
            do {
                $id = md5(uniqid('garuda_messages', 1));
                $db = DBManager::get()->query("SELECT `template_id` FROM `garuda_templates` "
                    . "WHERE `template_id` = '$id'");
            } while($db->fetch());
            // ... and update all relevant tables.
            DBManager::get()->execute("UPDATE `garuda_templates` SET `template_id` = :newid WHERE `template_id` = :oldid",
                array('newid' => $id, 'oldid' => $m['job_id']));
            DBManager::get()->execute("UPDATE `garuda_filters` SET `message_id` = :newid
                WHERE `message_id` = :oldid AND `type` = 'template'",
                array('newid' => $id, 'oldid' => $m['job_id']));
        }

        // As we have unique IDs now, we don't need the type distinction in garuda_filters table anymore.
        DBManager::get()->exec("ALTER TABLE `garuda_filters` DROP `type`");

        GarudaMessage::expireTableScheme();
    }

    public function down()
    {
        // Reintroduce "type" column in filters table and connect it to the corresponding entries.
        DBManager::get()->exec("ALTER TABLE `garuda_filters`
            ADD `type` ENUM ('message', 'template') NOT NULL DEFAULT 'message' AFTER `filter_id`");

        DBManager::get()->exec("UPDATE `garuda_filters` SET `type` = 'template'
            WHERE `message_id` IN (SELECT `template_id` FROM `garuda_templates`)");
    }
}
