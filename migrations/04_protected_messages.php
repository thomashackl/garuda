<?php
class ProtectedMessages extends Migration
{
    public function up(){
        DBManager::get()->exec("ALTER TABLE `garuda_messages` ADD `protected` TINYINT(1) NOT NULL DEFAULT 0 AFTER `done`");

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `garuda_messages` DROP `protected`");

        SimpleORMap::expireTableScheme();
    }

}
