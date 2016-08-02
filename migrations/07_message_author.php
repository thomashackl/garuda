<?php
require_once(realpath(__DIR__.'/../models/GarudaMessageToken.php'));
require_once(realpath(__DIR__.'/../models/GarudaMessage.php'));

class MessageAuthor extends Migration
{
    function up()
    {
        // Add new column for message author...
        DBManager::get()->exec("ALTER TABLE `garuda_messages` ADD `author_id` CHAR(32) NOT NULL AFTER `sender_id`");
        // ... and synchronize all found entries.
        DBManager::get()->exec("UPDATE `garuda_messages` SET `author_id` = `sender_id`");
        GarudaMessage::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `author_id`");
        GarudaMessage::expireTableScheme();
    }
}
