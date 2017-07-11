<?php
class InitGaruda extends Migration
{
    public function up(){
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_config` (
            `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id` COLLATE latin1_bin,
            `min_perm` ENUM ('dozent', 'admin') NOT NULL DEFAULT 'admin',
            `mkdate` INT NOT NULL DEFAULT 0,
            `chdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`institute_id`)
        )");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_inst_stg` (
            `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id` COLLATE latin1_bin,
            `abschluss_id` VARCHAR(32) REFERENCES `abschluss`.`abschluss_id` COLLATE latin1_bin,
            `studiengang_id` VARCHAR(32) REFERENCES `fach`.`fach_id` COLLATE latin1_bin,
            `mkdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`institute_id`, `abschluss_id`, `studiengang_id`),
            INDEX `institute_id` (`institute_id`),
            INDEX `degree` (`abschluss_id`),
            INDEX `subject` (`studiengang_id`)
        )");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_inst_inst` (
            `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id` COLLATE latin1_bin,
            `rec_inst_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id` COLLATE latin1_bin,
            `mkdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`institute_id`, `rec_inst_id`),
            INDEX `institute_id` (`institute_id`),
            INDEX `recipients` (`rec_inst_id`)
        )");
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `garuda_messages` (
            `job_id` INT AUTO_INCREMENT,
            `sender_id` VARCHAR(32) REFERENCES `auth_user_md5`.`user_id` COLLATE latin1_bin,
            `recipients` TEXT NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci,
            `subject` VARCHAR(255) NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci,
            `message` TEXT NOT NULL DEFAULT '' COLLATE utf8mb4_unicode_ci,
            `locked` BOOL DEFAULT 0, 
            `mkdate` INT NOT NULL DEFAULT 0,
            PRIMARY KEY (`job_id`),
            INDEX `sender` (`sender_id`),
            INDEX `ìn_progress` (`locked`)
        )");
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_config`");
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_inst_stg`");
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_inst_inst`");
        DBManager::get()->exec("DROP TABLE IF EXISTS `garuda_messages`");
    }

}
