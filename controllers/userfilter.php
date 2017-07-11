<?php
/**
 * userfilter.php
 *
 * Controller for filtering the allowed recipients by several criteria
 * (degree, subject or institute assignment, for example).
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

class UserfilterController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        Navigation::activateItem('/messaging/garuda/message');

        $this->filterfields = UserFilterField::getAvailableFilterFields();
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');

        PageLayout::setTitle($this->plugin->getDisplayName() .
            ' - ' . dgettext('garudaplugin', 'Personen filtern'));
    }

    public function add_action($type) {
        switch($type) {
            case 'employees':
                $this->filterfields = array(
                    'InstituteFilterField' => InstituteFilterField::getName(),
                    'StatusgroupFilterField' => StatusgroupFilterField::getName(),
                    'GenderFilterField' => GenderFilterField::getName(),
                    'PermissionFilterField' => PermissionFilterField::getName()
                );
                break;
            case 'students':
                $this->filterfields['GenderFilterField'] = GenderFilterField::getName();
                $this->filterfields['SelfAssignInstUserFilterField'] = SelfAssignInstUserFilterField::getName();
            default:
                break;
        }
    }

    public function addrestricted_action($type) {
        switch($type) {
            case 'employees':
                $this->filterfields = array(
                    'RestrictedInstituteFilterField' => array(
                        'name' => RestrictedInstituteFilterField::getName(),
                        'relation' => 'RestrictedStatusgroupFilterField'
                    ),
                    'RestrictedStatusgroupFilterField' => array(
                        'name' => RestrictedStatusgroupFilterField::getName(),
                        'relation' => 'RestrictedInstituteFilterField'
                    ),
                    'RestrictedGenderFilterField' => array(
                        'name' => RestrictedGenderFilterField::getName(),
                        'relation' => ''
                    ),
                    'RestrictedPermissionFilterField' => array(
                        'name' => RestrictedPermissionFilterField::getName(),
                        'relation' => ''
                    )
                );
                break;
            case 'students':
            default:
                $this->filterfields = array(
                    'RestrictedDegreeFilterField' => array(
                        'name' => RestrictedDegreeFilterField::getName(),
                        'relation' => 'RestrictedSubjectFilterField'
                    ),
                    'RestrictedSubjectFilterField' => array(
                        'name' => RestrictedSubjectFilterField::getName(),
                        'relation' => 'RestrictedDegreeFilterField'
                    ),
                    'RestrictedSemesterOfStudyFilterField' => array(
                        'name' => RestrictedSemesterOfStudyFilterField::getName(),
                        'relation' => ''
                    ),
                    'RestrictedGenderFilterField' => array(
                        'name' => RestrictedGenderFilterField::getName(),
                        'relation' => ''
                    ),
                    'RestrictedSelfAssignInstUserFilterField' => array(
                        'name' => RestrictedSelfAssignInstUserFilterField::getName(),
                        'relation' => ''
                    )
                );
        }
    }

    public function field_config_action($className) {
        if ($className) {
            list($fieldType, $param) = explode('_', $className);
            $this->field = new $fieldType($param);
        } else {
            $this->render_nothing();
        }
    }

    public function restricted_field_config_action($className, $restrictionCompare='', $restrictionValue='') {
        $this->field = new $className('', array('compare' => $restrictionCompare, 'value' => $restrictionValue));
    }

    public function save_action() {
        CSRFProtection::verifyUnsafeRequest();
        $filter = new UserFilter();
        $fields = Request::getArray('field');
        $compareOps = Request::getArray('compare_operator');
        $values = Request::getArray('value');

        for ($i=0 ; $i < sizeof($fields) ; $i++) {
            $className = $fields[$i];
            if ($className && $compareOps[$i] && isset($values[$i]) && $values[$i] !== '') {
                list($fieldType, $param) = explode('_', $className);
                $currentField = new $fieldType($param);
                $currentField->setCompareOperator($compareOps[$i]);
                $currentField->setValue($values[$i]);
                $filter->addField($currentField);
            }
        }
        $this->flash['sendto'] = Request::option('sendto');
        if (Request::option('sender')) {
            $this->flash['sender'] = Request::option('sender');
            if (Request::option('senderid')) {
                $this->flash['senderid'] = Request::option('senderid');
            }
        }
        if (Request::get('subject')) {
            $this->flash['subject'] = Request::get('subject');
        }
        if (Request::get('message')) {
            $this->flash['message'] = Request::get('message');
        }
        if (Request::getArray('filters')) {
            $filters = Request::getArray('filters');
        } else {
            $filters = array();
        }
        if ($filter->getFields()) {
            array_push($filters, serialize($filter));
        }
        $this->flash['filters'] = $filters;
        $this->relocate('message/write');
    }

    // customized #url_for for plugins
    public function url_for($to) {
        $args = func_get_args();
        // find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }
        // urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;
        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    } 
}
