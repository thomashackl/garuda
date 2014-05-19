<?php
class CronjobsDone extends DBMigration
{
    function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages` ADD `done` TINYINT AFTER `locked`");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `done`");
    }

}