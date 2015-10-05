<?php
class LongtextForRecipients extends DBMigration
{
    function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `recipients` `recipients` LONGTEXT NOT NULL");
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `recipients` `recipients` TEXT NOT NULL");
    }

}
