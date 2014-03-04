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
 * @category    Stud.IP
 */

class GarudaModel {
    
    public static function getConfiguration($instituteIds) {
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
                    INNER JOIN `studiengaenge` s ON (gis.`studiengang_id`=s.`studiengang_id`)
                WHERE gis.`institute_id`=:id
                ORDER BY a.`name` ASC, s.`name` ASC");
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
    public static function getConfigurationForUser($userId) {
        $userConfig = array(
            'studycourses' => array(),
            'institutes' => array()
        );
        $userInsts = array_map(function($i) { return $i['Institut_id']; }, Institute::getMyInstitutes($userId));
        $config = self::getConfiguration($userInsts);
        foreach ($userInsts as $i) {
            if (!$GLOBALS['perm']->have_studip_perm($config[$i]['min_perm'], $i, $userId)) {
                unset($config[$i]);
            }
        }
        // Get allowed study courses.
        $userConfig['studycourses'] = DBManager::get()->fetchAll("SELECT a.`abschluss_id`, a.`name` AS degree, s.`studiengang_id`, s.`name` AS subject
            FROM `garuda_inst_stg` gis
                INNER JOIN `abschluss` a ON (gis.`abschluss_id`=a.`abschluss_id`)
                INNER JOIN `studiengaenge` s ON (gis.`studiengang_id`=s.`studiengang_id`)
            WHERE (gis.`institute_id` IN (:ids))
            ORDER BY degree ASC, subject ASC", array('ids' => $userInsts));
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
        $allowed = array();
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

    public static function saveConfiguration($instituteId, $minPerm, $assignedStudycourses, $assignedInstitutes) {
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

    /**
     * Creates a table entry for the Garuda cronjob containing the desired
     * message and intended recipients.
     * 
     * @param String $sender     Who sends this message?
     * @param array  $recipients Intended recipients for this message
     *                           (array of Stud.IP user IDs)
     * @param String $subject    Message subject
     * @param String $message    Message text
     */
    public static function createCronEntry($sender, $recipients, $subject, $message) {
        $stmt = DBManager::get()->prepare("INSERT INTO `garuda_messages`
            (`sender_id`, `recipients`, `subject`, `message`, `mkdate`)
            VALUES
            (:sender, :rec, :subject, :message, UNIX_TIMESTAMP())");
        return $stmt->execute(array(
            'sender' => $GLOBALS['user']->id,
            'rec' => json_encode($recipients),
            'subject' => $subject,
            'message' => $message)
        );
    }

    /**
     * Gets all cron entries that are not already locked by a cron instance still running.
     * 
     * @return Array of found entries to be processed by cron.
     */
    public static function getCronEntries() {
        return DBManager::get()->fetchAll("SELECT * FROM `garuda_messages` WHERE `locked`=0 ORDER BY `mkdate`", array());
    }

    /**
     * Locks the given cron job entry. 
     * 
     * 
     * @param int $entryId entry to be locked
     * @return Successfully locked?
     */
    public static function lockCronEntry($entryId) {
        return DBManager::get()->execute("UPDATE `garuda_messages` SET `locked`=1 WHERE `job_id`=:id", array('id' => $entryId));
    }

}
