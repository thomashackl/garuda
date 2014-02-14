<?php
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
        PageLayout::addScript($this->dispatcher->current_plugin->getPluginURL().'/assets/garuda.js');
        Navigation::activateItem('/messaging/garuda/configuration');
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
        $info = array();
        $info[] = array(
                      "icon" => "icons/16/black/doctoral_cap.png",
                      "text" => "Hier können Sie pro Einrichtung einstellen, ".
                                "welche Studiengänge als Zielgruppe für ".
                                "Nachrichten erlaubt sind.");
        $info[] = array(
                      "icon" => "icons/16/black/info.png",
                      "text" => "Pro Einrichtung kann festgelegt werden, ob ".
                                "Personen die Berechtigung 'dozent' oder ".
                                "'admin' haben müssen, um diese Funktion zu ".
                                "nutzen.");
        $infotext = array(
            array("kategorie" => _('Informationen:'),
                  "eintrag" => $info
            )
        );
        $this->infobox = array('content' => $infotext,
                         'picture' => 'infobox/administration.jpg'
        );
    }

    public function get_action($instituteId) {
        //CSRFProtection::verifyUnsafeRequest();
        $config = GarudaModel::getConfiguration(array($instituteId));
        $this->config = $config[$instituteId];
        $this->degrees = StudycourseModel::getStudyDegrees();
    }

    public function save_action() {
        CSRFProtection::verifyUnsafeRequest();
        $studycourses = array_map(function($entry) {
                $data = explode('|', $entry);
                return array('degree' => $data[0], 'profession' => $data[1]);
            }, Request::getArray('studycourses'));
        if (GarudaModel::saveConfiguration(Request::option('institute'), Request::option('perm'), $studycourses)) {
            $this->flash['success'] = _('Die Änderungen wurden gespeichert.');
        } else {
            $this->flash['error'] = _('Die Änderungen konnten nicht gespeichert werden.');
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
