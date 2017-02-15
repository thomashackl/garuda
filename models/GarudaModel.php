<?php

/**
 * GarudaModel.php
 *
 * Model class for the Garuda mailing plugin.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Garuda
 */

class GarudaModel {

    /**
     * Fetches the configuration for the given institute IDs:
     * Persons having at least which permission level may write messages to whom?
     *
     * @param mixed $instituteIds the institutes to check
     */
    public static function getConfiguration($instituteIds)
    {
        $config = array();
        $db = DBManager::get();
        $data = $db->fetchAll("SELECT * FROM `garuda_config` WHERE `institute_id` IN (?)", array($instituteIds));
        foreach ($data as $entry) {
            $config[$entry['institute_id']] = array(
                'min_perm' => $entry['min_perm'],
                'studycourses' => array(),
                'institutes' => array()
            );
            $stmt = $db->prepare("SELECT gis.*
                FROM `garuda_inst_stg` gis
                    INNER JOIN `abschluss` a ON (gis.`abschluss_id`=a.`abschluss_id`)
                    INNER JOIN `fach` f ON (gis.`studiengang_id`=f.`fach_id`)
                WHERE gis.`institute_id`=:id
                ORDER BY a.`name` ASC, f.`name` ASC");
            $stmt->execute(array('id' => $entry['institute_id']));
            while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $config[$entry['institute_id']]['studycourses'][$current['abschluss_id']][$current['studiengang_id']] = true;
            }
            $stmt = $db->prepare("SELECT gi.*
                FROM `garuda_inst_inst` gi
                    INNER JOIN `Institute` i ON (gi.`rec_inst_id`=i.`Institut_id`)
                WHERE gi.`institute_id`=:id
                ORDER BY i.`Name` ASC");
            $stmt->execute(array('id' => $entry['institute_id']));
            while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $config[$entry['institute_id']]['institutes'][$current['rec_inst_id']] = true;
            }
        }
        foreach ($instituteIds as $id) {
            if (!$config[$id]) {
                $config[$id] = array('min_perm' => 'admin', 'studycourses' => array(), 'institutes' => array());
            }
        }
        return $config;
    }

    /**
     * Fetches the configuration settings for a given user.
     *
     * @param String $userId user to check
     * @return array The courses of study and institutes the user may set as recipients.
     */
    public static function getConfigurationForUser($userId)
    {
        $userConfig = array(
            'studycourses' => array(),
            'institutes' => array()
        );
        $userInsts = DBManager::get()->fetchFirst(
            "SELECT DISTINCT `Institut_id` FROM `user_inst`
            WHERE `user_id` = ? AND `inst_perms` NOT IN ('user', 'autor')",
            array($userId));
        $config = self::getConfiguration($userInsts);
        foreach ($userInsts as $i) {
            if (!$GLOBALS['perm']->have_studip_perm($config[$i]['min_perm'], $i, $userId)) {
                unset($config[$i]);
            }
        }
        // Get allowed study courses.
        $userConfig['studycourses'] = DBManager::get()->fetchAll("SELECT a.`abschluss_id`, a.`name` AS degree, f.`fach_id`, f.`name` AS subject
            FROM `garuda_inst_stg` gis
                INNER JOIN `abschluss` a ON (gis.`abschluss_id`=a.`abschluss_id`)
                INNER JOIN `fach` f ON (gis.`studiengang_id`=f.`fach_id`)
            WHERE (gis.`institute_id` IN (:ids))
            ORDER BY degree ASC, subject ASC", array('ids' => array_keys($config)));
        // Get allowed institutes (user's own institutes are always allowed).
        $institutes = DBManager::get()->fetchAll("SELECT i.`Institut_id`
            FROM `Institute` i
                INNER JOIN `Institute` f ON (i.`fakultaets_id`=f.`Institut_id`)
            WHERE (i.`Institut_id` IN (
                    SELECT `rec_inst_id` FROM `garuda_inst_inst`
                    WHERE `institute_id` IN (:ids)
                )
                OR i.`fakultaets_id` IN (
                    SELECT `rec_inst_id` FROM `garuda_inst_inst`
                    WHERE `institute_id` IN (:ids)
                )
                OR i.`Institut_id` IN (:ids))
                AND i.`fakultaets_id` != ''
            ORDER BY f.`Name` ASC, i.`Name` ASC", array('ids' => array_keys($config)));
        foreach ($institutes as $inst) {
            $i = new Institute($inst['Institut_id']);
            $userConfig['institutes'][$inst['Institut_id']] = array(
                'id' => $i->Institut_id,
                'name' => $i->Name,
                'faculty' => $i->fakultaets_id,
                'is_fak' => $i->is_fak
            );
        }
        return $userConfig;
    }

    public static function saveConfiguration($instituteId, $minPerm, $assignedStudycourses, $assignedInstitutes)
    {
        $success = true;
        $db = DBManager::get();
        $stmt = $db->prepare("INSERT INTO `garuda_config` (`institute_id`, `min_perm`, `mkdate`, `chdate`)
            VALUES (:id, :perm, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
            ON DUPLICATE KEY UPDATE `min_perm`=VALUES(`min_perm`), `chdate`=VALUES(`chdate`)");
        $success = ($success && $stmt->execute(array('id' => $instituteId, 'perm' => $minPerm)));
        $stmt = $db->prepare("DELETE FROM `garuda_inst_stg` WHERE `institute_id`=:id");
        $success = ($success && $stmt->execute(array('id' => $instituteId)));
        if ($assignedStudycourses) {
            $query = "INSERT INTO `garuda_inst_stg` (`institute_id`, `abschluss_id`, `studiengang_id`, `mkdate`) VALUES ";
            $i = 0;
            $parameters = array('id' => $instituteId);
            foreach ($assignedStudycourses as $entry) {
                if ($i > 0) {
                    $query .= ", ";
                }
                $query .= "(:id, :abschluss".$i.", :studiengang".$i.", UNIX_TIMESTAMP()) ";
                $parameters['abschluss'.$i] = $entry['degree'];
                $parameters['studiengang'.$i] = $entry['subject'];
                $i++;
            }
            $query .= "ON DUPLICATE KEY UPDATE `mkdate`=VALUES(`mkdate`)";
            $stmt = $db->prepare($query);
            $success = ($success && $stmt->execute($parameters));
        }
        $stmt = $db->prepare("DELETE FROM `garuda_inst_inst` WHERE `institute_id`=:id");
        $success = ($success && $stmt->execute(array('id' => $instituteId)));
        if ($assignedInstitutes) {
            $query = "INSERT INTO `garuda_inst_inst` (`institute_id`, `rec_inst_id`, `mkdate`) VALUES ";
            $i = 0;
            $parameters = array('id' => $instituteId);
            foreach ($assignedInstitutes as $entry) {
                if ($i > 0) {
                    $query .= ", ";
                }
                $query .= "(:id, :inst".$i.", UNIX_TIMESTAMP()) ";
                $parameters['inst'.$i] = $entry;
                $i++;
            }
            $query .= "ON DUPLICATE KEY UPDATE `mkdate`=VALUES(`mkdate`)";
            $stmt = $db->prepare($query);
            $success = ($success && $stmt->execute($parameters));
        }
        return $success;
    }

    public static function calculateUsers($userId, $target, $config)
    {
        switch ($target) {
            case 'all':
                return self::getAllUsers($userId, $config);
            case 'students':
                return self::getAllStudents($userId, $config);
            case 'employees':
                return self::getAllEmployees($userId, $config);
        }
    }

    public static function getAllUsers($userId, $config)
    {
    	return array_merge(self::getAllStudents($userId, $config), self::getAllEmployees($userId, $config));
    }

    public static function getAllStudents($userId, $config)
    {
        if ($GLOBALS['perm']->have_perm('root', $userId)) {
            return DBManager::get()->fetchFirst(
                "SELECT DISTINCT `user_id` FROM `user_studiengang` WHERE `fach_id`!='21979dd6cc8bcb2138f333506dc30ffb'");
        } else {
            $query = "SELECT DISTINCT `user_id` FROM `user_studiengang`";
            $parameters = array();
            $where = "";
            if ($config['studycourses']) {
                $query .= " WHERE ";
                foreach ($config['studycourses'] as $entry) {
                    if ($where) {
                        $where .= " OR ";
                    }
                    $where .=  "(`abschluss_id`=? AND `fach_id`=?)";
                    $parameters[] = $entry['abschluss_id'];
                    $parameters[] = $entry['fach_id'];
                }
                $query .= $where;
            }
            return DBManager::get()->fetchFirst($query, $parameters);
        }
        return array();
    }

    public static function getAllEmployees($userId, $config)
    {
        if ($GLOBALS['perm']->have_perm('root', $userId)) {
            return DBManager::get()->fetchFirst("SELECT DISTINCT `user_id` " .
                "FROM `user_inst` WHERE `inst_perms` IN ('autor', 'tutor', 'dozent')");
        } else {
            $query = "SELECT DISTINCT `user_id` FROM `user_inst` ".
                "WHERE `inst_perms` IN ('autor', 'tutor', 'dozent')";
            $parameters = array();
            // Add own institutes first.
            $institutes = array_flip(array_map(function($i) { return $i['Institut_id']; }, Institute::getMyInstitutes($userId)));
            if ($config['institutes']) {
                $institutes = array_merge($institutes, $config['institutes']);
            }
            if ($institutes) {
                $query .= " AND `Institut_id` IN (?)";
                $parameters[] = array_keys($institutes);
            }
            return DBManager::get()->fetchFirst($query, $parameters);
        }
    }

    public static function extractTokens($file)
    {
        $tokens = array();
        ini_set("auto_detect_line_endings", true);
        $handle = fopen($file, 'r');
        if ($handle) {
            while (!feof($handle)) {
                $line = trim(fgets($handle));
                if ($line) {
                    $tokens[] = $line;
                }
            }
        }
        return $tokens;
    }

    /**
     * Fetches all assigned tokens for a given cron job entry.
     *
     * @param  int   $entryId entry to fetch tokens for
     * @param  bool  $only_assigned fetch only tokens that are assigned to a user ID.
     * @return array All tokens that were found for the given cron job entry.
     */
    public static function getTokens($entryId, $only_assigned=false)
    {
        $tokens = array();
        if ($only_assigned) {
            $query = "SELECT * FROM `garuda_tokens` WHERE `job_id`=? AND `user_id` IS NOT NULL ORDER BY `token_id`";
        } else {
            $query = "SELECT * FROM `garuda_tokens` WHERE `job_id`=? ORDER BY `token_id`";
        }
        $data = DBManager::get()->fetchAll($query, array($entryId));
        foreach ($data as $entry) {
            $tokens[$entry['user_id']] = $entry['token'];
        }
        return $tokens;
    }

    /**
     * Fetches all sent messages that have tokens assigned.
     *
     * @return An array of messages.
     */
    public static function getMessagesWithTokens() {
        return DBManager::get()->fetchAll("SELECT DISTINCT m.*
            FROM `garuda_messages` m
            WHERE m.`done`=1
                AND EXISTS (SELECT DISTINCT `job_id` FROM `garuda_tokens` WHERE `job_id`=m.`job_id`)
            ORDER BY m.`mkdate` DESC");
    }

}
