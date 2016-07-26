<?php

/**
 * RestrictedDegreeFilterField.class.php
 * 
 * All conditions concerning the study degree in Stud.IP can be specified here.
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

require_once('lib/classes/admission/userfilter/DegreeCondition.class.php');

class RestrictedDegreeFilterField extends DegreeCondition
{
    public $config = array();

    /**
     * @see UserFilterField::_construct
     */
    public function __construct($fieldId='', $restriction=array()) {
        parent::__construct($fieldId);

        // Get Garuda configuration:
        // Find out which user this filter belongs to...
        $filter = GarudaFilter::findOneByFilter_id($this->conditionId);
        // ... and load Garuda config for this user.
        $this->config = GarudaModel::getConfigurationForUser($filter->user_id);

        if ($restriction['compare'] == '=') {
            $restriction['compare'] = '==';
        }
        foreach($this->config['studycourses'] as $entry) {
            if (!$restriction['value'] || ($restriction && eval("return ('".$entry['studiengang_id']."'".$restriction['compare']."'".$restriction['value']."');"))) {
                $d = new Degree($entry['abschluss_id']);
                $this->validValues[$entry['abschluss_id']] = $d->name;
            }
        }
    }
}
