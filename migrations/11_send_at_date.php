<?php

require_once(realpath(__DIR__.'/../models/GarudaMessage.php'));

class SendAtDate extends Migration
{
    public function up()
    {
        /*
         * Add new column to filters table for specifying an (optional) sending date.
         */
        DBManager::get()->exec("ALTER TABLE `garuda_messages`
          ADD `send_date` INT NULL DEFAULT NULL AFTER `author_id`");
        GarudaMessage::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `send_date`");
        GarudaMessage::expireTableScheme();
    }
}
