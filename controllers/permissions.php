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
        PageLayout::setTitle(dgettext('garudaplugin', 'Berechtigungen'));

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
        $config = GarudaModel::getConfiguration(array($instituteId));
        $this->config = $config[$instituteId];
        $this->studycourses = array();
        foreach (Degree::findBySQL("1 ORDER BY `name`") as $degree) {
            $assigned = DBManager::get()->fetchFirst(
                "SELECT DISTINCT `fach_id` FROM `user_studiengang` WHERE `abschluss_id` = ?",
                array($degree->id));
            $subjects = StudyCourse::findMany($assigned, "ORDER BY `name`");
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

    /**
     *
     */
    public function external_db_action()
    {
        $this->enabled = Config::get()->GARUDA_PERMISSIONS_EXTERNAL_DB_ENABLE;
        $this->config = Config::get()->GARUDA_PERMISSIONS_EXTERNAL_DB_SETTINGS;
    }

    public function save_external_db_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::option('enable')) {
            $config = array(
                'dbtype' => Request::option('dbtype'),
                'hostname' => Request::get('hostname'),
                'database' => Request::get('database'),
                'username' => Request::get('username'),
                'password' => Request::get('password'),
                'table' => Request::get('table'),
                'degrees' => Request::get('degrees'),
                'subjects' => Request::get('subjects'),
                'institutes' => Request::get('institutes')
            );
            if (Request::option('dbtype') == 'informix') {
                $config['informixdir'] = Request::get('informixdir');
                $config['protocol'] = Request::option('protocol');
                $config['service'] = Request::option('service');
                $config['server'] = Request::option('server');
                $config['client_locale'] = Request::get('client_locale');
                $config['db_locale'] = Request::get('db_locale');
            }
        } else {
            $config = array();
        }

        $success = false;

        Config::get()->store('GARUDA_PERMISSIONS_EXTERNAL_DB_ENABLE', Request::option('enable') ? 1 : 0);

        if (Config::get()->store('GARUDA_PERMISSIONS_EXTERNAL_DB_SETTINGS', ['value' => $config])) {
            $success = true;
        }

        if ($success) {
            PageLayout::postSuccess(
                dgettext('garudaplugin', 'Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(
                dgettext('garudaplugin', 'Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->redirect($this->url_for('permissions/external_db'));
    }

    public function match_institutes_action()
    {
        $this->institutes = GarudaModel::getInstitutesFromExternalDB();
        $this->studip_institutes = Institute::getInstitutes();
    }

    public function assign_institutes_action()
    {
        if (Config::get()->store('GARUDA_PERMISSIONS_EXTERNAL_DB_SETTINGS',
                ['value' => Request::getArray('institutes')])) {
            PageLayout::postSuccess(dgettext('garudaplugin', 'Die Einrichtungen wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('garudaplugin', 'Die Einrichtungen konnten nicht gespeichert werden.'));
        }
        $this->redirect($this->url_for('permissions/external_db'));
    }

    public function do_import()
    {

    }

    // customized #url_for for plugins
    public function url_for($to = '') {
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
