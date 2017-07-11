<?php
/**
 * permissions.php
 * 
 * Permission functionality for Garuda: who may send messages to whom?
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

class PermissionsController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $GLOBALS['perm']->check('root');

        $this->current_action = $action;
        $this->validate_args($args);
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        Navigation::activateItem('/messaging/garuda/permissions');
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');
    }

    public function index_action() {
        PageLayout::setTitle($this->plugin->getDisplayName() .
            ' - ' . dgettext('garudaplugin', 'Berechtigungen'));

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
                "'admin' haben müssen, um diese Funktion zu ".
                "nutzen."),
            'icons/16/white/lock-locked.png');
        Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Konfiguration'),
                            dgettext('garudaplugin', "Hier können Sie pro Einrichtung einstellen, ".
                                "welche Studiengänge als Zielgruppe für ".
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
        $this->studycourses = array();
        foreach (Degree::findBySQL("1 ORDER BY `name`") as $degree) {
            $assigned = DBManager::get()->fetchFirst(
                "SELECT DISTINCT `studiengang_id` FROM `user_studiengang` WHERE `abschluss_id` = ?",
                array($degree->id));
            $subjects = SimpleORMapCollection::createFromArray(StudyCourse::findMany($assigned))->orderBy('name');
            $this->studycourses[$degree->id] = array(
                'name' => $degree->name,
                'subjects' => $subjects
            );
        }

        $this->institutes = Institute::getInstitutes();
    }

    public function save_action() {
        CSRFProtection::verifyUnsafeRequest();
        $studycourses = array_map(function($entry) {
                $data = explode('|', $entry);
                return array('degree' => $data[0], 'subject' => $data[1]);
            }, Request::getArray('studycourses'));
        if (GarudaModel::saveConfiguration(Request::option('institute'), Request::option('perm'), $studycourses, Request::getArray('institutes'))) {
            PageLayout::postSuccess(dgettext('garudaplugin', 'Die Änderungen wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('garudaplugin', 'Die Änderungen konnten nicht gespeichert werden.'));
        }
        $this->flash['institute_id'] = Request::option('institute');
        $this->relocate('permissions');
    }

    // customized #url_for for plugins
    public function url_for($to) {
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
