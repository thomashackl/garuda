CREATE TABLE IF NOT EXISTS `garuda_config` (
    `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id`,
    `min_perm` ENUM ('dozent', 'admin') NOT NULL DEFAULT 'admin',
    `mkdate` INT NOT NULL DEFAULT 0,
    `chdate` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`institute_id`)
);

CREATE TABLE IF NOT EXISTS `garuda_inst_stg` (
    `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id`,
    `abschluss_id` VARCHAR(32) REFERENCES `abschluss`.`abschluss_id`,
    `studiengang_id` VARCHAR(32) REFERENCES `studiengang`.`studiengang_id`,
    `mkdate` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`institute_id`, `abschluss_id`, `studiengang_id`),
    INDEX `institute_id` (`institute_id`),
    INDEX `degree` (`abschluss_id`)
    INDEX `subject` (`studiengang_id`)
);

CREATE TABLE IF NOT EXISTS `garuda_inst_inst` (
    `institute_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id`,
    `rec_inst_id` VARCHAR(32) REFERENCES `Institute`.`Institut_id`,
    `mkdate` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`institute_id`, `rec_inst_id`),
    INDEX `institute_id` (`institute_id`),
    INDEX `recipients` (`rec_inst_id`)
);

CREATE TABLE IF NOT EXISTS `garuda_messages` (
    `job_id` INT AUTO_INCREMENT,
    `sender_id` VARCHAR(32) REFERENCES `auth_user_md5`.`user_id`,
    `recipients` TEXT NOT NULL DEFAULT '',
    `subject` VARCHAR(255) NOT NULL DEFAULT '',
    `message` TEXT NOT NULL DEFAULT '',
    `locked` BOOL DEFAULT 0, 
    `mkdate` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`job_id`),
    INDEX `sender` (`sender_id`),
    INDEX `ìn_progress` (`locked`)
);