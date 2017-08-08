<?php
class ReplacementTokens extends Migration
{
    public function up(){
        DBManager::get()->exec("CREATE TABLE `garuda_tokens` (
            `token_id` INT NOT NULL AUTO_INCREMENT,
            `job_id` VARCHAR(32) NOT NULL COLLATE latin1_bin REFERENCES `garuda_messages`.`job_id`,
            `user_id` VARCHAR(32) NULL COLLATE latin1_bin REFERENCES `auth_user_md5`.`user_id`,
            `token` VARCHAR(1000) NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci,
            `mkdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`token_id`),
            INDEX `job` (`job_id`)
        )");
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE `garuda_tokens`");
    }

}
