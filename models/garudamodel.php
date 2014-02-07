<?php

class GarudaModel {
    
    public static function getConfiguration($instituteId) {
        $db = DBManager::get();
        $stmt = $db->prepare("SELECT * FROM `garuda_config` WHERE `institute_id`=:id LIMIT 1");
        $stmt->bindParam('id', $instituteId);
        $stmt->execute();
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config = array(
                'min_perm' => $data['min_perm'],
                'studycourses' => array()
            );
            $stmt = $db->prepare("SELECT * FROM `garuda_inst_stg` WHERE `institute_id`=:id");
            $stmt->bindParam('id', $instituteId);
            $stmt->execute();
            while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $config['studycourses'][$current['abschluss_id']][$current['studiengang_id']] = true;
            }
        } else {
            $config = array(
                'min_perm' => 'admin',
                'studycourses' => array()
            );
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
