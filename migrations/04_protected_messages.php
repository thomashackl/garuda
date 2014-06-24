<?php
class ProtectedMessages extends DBMigration
{
    function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages`
            ADD `protected` TINYINT(1) AFTER `done` NOT NULL DEFAULT 0");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `protected`");
    }

}
