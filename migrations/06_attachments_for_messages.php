<?php
class AttachmentsForMessages extends Migration
{
    public function up()
    {
        DBManager::get()->exec('ALTER TABLE `garuda_messages` ADD `attachment_id` VARCHAR(32) AFTER `message`');

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec('ALTER TABLE `garuda_messages` DROP `attachment_id`');

        SimpleORMap::expireTableScheme();
    }
}
