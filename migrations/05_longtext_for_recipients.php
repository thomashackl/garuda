<?php
class LongtextForRecipients extends DBMigration
{
    public function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `recipients` `recipients` LONGTEXT NOT NULL");
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `recipients` `recipients` TEXT NOT NULL");
    }

}
