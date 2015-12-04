<?php
class AttachmentsForMessages extends Migration
{
    function up()
    {
        DBManager::get()->exec('ALTER TABLE `garuda_messages` ADD `attachment_id` VARCHAR(32) AFTER `message`');
    }

    function down()
    {
        DBManager::get()->exec('ALTER TABLE `garuda_messages` DROP `attachment_id`');
    }
}
