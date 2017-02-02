<?php
class CronjobsDone extends Migration
{
    public function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages` ADD `done` TINYINT NOT NULL DEFAULT 0 AFTER `locked`");
        DBManager::get()->exec("UPDATE `garuda_messages` SET `done`=1 WHERE `locked`=1");
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `done`");
    }

}
