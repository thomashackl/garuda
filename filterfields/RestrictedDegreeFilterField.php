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
 * @category    Stud.IP
 */

require_once('lib/classes/admission/userfilter/DegreeCondition.class.php');

class RestrictedDegreeFilterField extends DegreeCondition
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'abschluss';
    public $valuesDbIdField = 'abschluss_id';
    public $valuesDbNameField = 'name';
    public $userDataDbTable = 'user_studiengang';
    public $userDataDbField = 'abschluss_id';

    /**
     * @see UserFilterField::_construct
     */
    public function __construct($fieldId='', $restrictionValue='') {
        parent::__construct($fieldId);
        $this->validValues = array();
        $this->config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        $this->validValues['all'] = _('alle');
        foreach($this->config['studycourses'] as $entry) {
            if (!$restrictionValue || $entry['studiengang_id'] == $restrictionValue) {
                $d = new Degree($entry['abschluss_id']);
                $this->validValues[$entry['abschluss_id']] = $d->name;
            }
        }
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return _("Abschluss");
    }

} /* end of class RestrictedDegreeFilterField */

?>