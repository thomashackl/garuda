<?php
/**
 * configuration.php
 * 
 * Configuration functionality for Garuda: who may send messages to whom?
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

require_once('app/models/studycourse.php');

class ConfigurationController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $GLOBALS['perm']->check('root');
        $this->current_action = $action;
        $this->validate_args($args);
        $this->flash = Trails_Flash::instance();
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
        Navigation::activateItem('/messaging/garuda/configuration');
        $this->set_content_type('text/html;charset=windows-1252');
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');
    }

    public function index_action() {
        $this->faculties = Institute::findBySQL("`Institut_id`=`fakultaets_id`");
        usort($this->faculties,
            function($a, $b) {
                if ($a->name == $b->name) {
                    return 0;
                }
                return strnatcasecmp($a->name, $b->name);
            }
        );
        Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Berechtigung'),
            dgettext('garudaplugin', "Pro Einrichtung kann festgelegt werden, ob ".
                "Personen die Berechtigung 'dozent' oder ".
                "'admin' haben m�ssen, um diese Funktion zu ".
                "nutzen."),
            'icons/16/white/lock-locked.png');
        Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Konfiguration'),
                            dgettext('garudaplugin', "Hier k�nnen Sie pro Einrichtung einstellen, ".
                                "welche Studieng�nge als Zielgruppe f�r ".
                                "Nachrichten erlaubt sind."),
                            'icons/16/white/doctoral_cap.png');
        Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Einrichtungen'),
                            dgettext('garudaplugin', "Welche Einrichtungen sind neben den eigenen ".
                                "als Zielgruppe erlaubt?"),
                            'icons/16/white/institute.png');
    }

    public function get_action($instituteId) {
        //CSRFProtection::verifyUnsafeRequest();
        $config = GarudaModel::getConfiguration(array($instituteId));
        $this->config = $config[$instituteId];
        $this->degrees = StudycourseModel::getStudyDegrees();
        $this->institutes = Institute::getInstitutes();
    }

    public function save_action() {
        CSRFProtection::verifyUnsafeRequest();
        $studycourses = array_map(function($entry) {
                $data = explode('|', $entry);
                return array('degree' => $data[0], 'subject' => $data[1]);
            }, Request::getArray('studycourses'));
        if (GarudaModel::saveConfiguration(Request::option('institute'), Request::option('perm'), $studycourses, Request::getArray('institutes'))) {
            $this->flash['success'] = dgettext('garudaplugin', 'Die �nderungen wurden gespeichert.');
        } else {
            $this->flash['error'] = dgettext('garudaplugin', 'Die �nderungen konnten nicht gespeichert werden.');
        }
        $this->flash['institute_id'] = Request::option('institute');
        $this->redirect($this->url_for('configuration'));
    }

    // customized #url_for for plugins
    function url_for($to) {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join("/", $args));
    } 
}
