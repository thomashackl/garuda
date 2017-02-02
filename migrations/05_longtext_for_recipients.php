<?php
class LongtextForRecipients extends Migration
{
    public function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `recipients` `recipients` LONGTEXT NOT NULL");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` CHANGE `recipients` `recipients` TEXT NOT NULL");

        SimpleORMap::expireTableScheme();
    }

}
