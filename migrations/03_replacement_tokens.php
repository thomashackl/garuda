<?php
class ReplacementTokens extends DBMigration
{
    function up(){
        DBManager::get()->exec("CREATE TABLE `garuda_tokens` (
            `token_id` INT NOT NULL AUTO_INCREMENT,
            `job_id` VARCHAR(32) NOT NULL REFERENCES `garuda_messages`.`job_id`,
            `user_id` VARCHAR(32) REFERENCES `auth_user_md5`.`user_id` DEFAULT NULL,
            `token` VARCHAR(255) NOT NULL DEFAULT '',
            `mkdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`token_id`),
            INDEX `job` (`job_id`)
        ) ENGINE=InnoDB COLLATE latin1_german1_ci CHARACTER SET latin1");
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE `garuda_tokens`");
    }

}