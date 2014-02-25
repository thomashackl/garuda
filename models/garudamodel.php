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
                'studycourses' => array()
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
        }
        foreach ($instituteIds as $id) {
            if (!$config[$id]) {
                $config[$id] = array('min_perm' => 'admin', 'studycourses' => array());
            }
        }
        return $config;
    }

    public static function getConfigurationForUser($userId) {
        $userInsts = array_map(function($i) { return $i['Institut_id']; }, Institute::getMyInstitutes($userId));
        $config = self::getConfiguration($userInsts);
        $institutes = array();
        foreach ($userInsts as $i) {
            if (!$GLOBALS['perm']->have_studip_perm($config[$i]['min_perm'], $i, $userId)) {
                unset($config[$i]);
            }
        }
        return $config;
    }

    public static function saveConfiguration($instituteId, $minPerm, $assignedStudycourses) {
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
                $parameters['studiengang'.$i] = $entry['profession'];
                $i++;
            }
            $query .= "ON DUPLICATE KEY UPDATE `mkdate`=VALUES(`mkdate`)";
            $stmt = $db->prepare($query);
            $success = ($success && $stmt->execute($parameters));
        }
        return $success;
    }

}
