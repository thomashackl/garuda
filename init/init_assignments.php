<?php
//require_once(realpath(dirname(__FILE__).'../../../../cli/studip_cli_env.inc.php'));
require_once('studip_cli_env.inc.php');

// Map HISSOS faculty short names to Stud.IP Institut_id.
$faculties = array(
    'JU' => 'f03',
    'KT' => 'f05',
    'MI' => 'f01',
    'PH' => 'f05',
    'WW' => 'f04'
);
$degrees = array(
    'Promotion mit Abschluss' => '4709d072ebe9b852b37c101f51718626',
    'Promotion Grad.kolleg' => '68ace518672d662f5f471ff27ce93375'
);
$subjects = array();

// Initialize degrees and subjects from Stud.IP database.
$data = DBManager::get()->fetchAll("SELECT `abschluss_id`, `name` FROM `abschluss` WHERE `name`!='' ORDER BY `name` ASC");
foreach ($data as $row) {
    $degrees[$row['name']] = $row['abschluss_id'];
}
$data = DBManager::get()->fetchAll("SELECT `studiengang_id`, `name` FROM `studiengaenge` WHERE `name`!='' ORDER BY `name` ASC");
foreach ($data as $row) {
    $subjects[$row['name']] = $row['studiengang_id'];
}

// Create faculty configuration if necessary.
foreach ($faculties as $his => $studip) {
    DBManager::get()->execute("INSERT IGNORE INTO `garuda_config` (`institute_id`, `min_perm`, `mkdate`, `chdate`) VALUES (?, 'admin', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())", array($studip));
}

// Read CSV file with studycourse assignments.
$h = fopen('export_fb.csv', 'r');
$inserts = "";
$parameters = array();
$processed = array();
while (!feof($h)) {
    $line = fgetcsv($h);
    if ($line) {
        if ($degrees[$line[0]] && $subjects[$line[1]] && $faculties[$line[2]] && !$processed[$line[0].'|'.$line[1].'|'.$line[2]]) {
            if ($inserts) {
                $inserts .= ", ";
            }
            $inserts .= "(?, ?, ?, UNIX_TIMESTAMP())";
            $parameters[] = $faculties[$line[2]];
            $parameters[] = $degrees[$line[0]];
            $parameters[] = $subjects[$line[1]];
            $processed[$line[0].'|'.$line[1].'|'.$line[2]] = true;
        }
    }
}

$query = "INSERT IGNORE INTO `garuda_inst_stg` (`institute_id`, `abschluss_id`, `studiengang_id`, `mkdate`) VALUES ".$inserts;
DBManager::get()->execute($query, $parameters);
