<?php
class MessageController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $this->current_action = $action;
        $this->validate_args($args);
        $this->flash = Trails_Flash::instance();
        if (Request::isXhr()) {
            $this->set_layout(null);
            header('Content-Type: text/html; charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
        Navigation::activateItem('/messaging/garuda/message');
        $institutes = array_map(function($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());
        $this->config = GarudaModel::getConfiguration($institutes);
        foreach ($institutes as $i) {
            if ($GLOBALS['perm']->have_studip_perm($this->config[$i]['min_perm'], $i)) {
                $this->institutes[] = $i;
            }
        }
        $this->i_am_root = false;
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->i_am_root = true;
            $this->filterfields = UserFilterField::getAvailableFilterFields();
        }
    }

    public function index_action() {
        $info = array();
        $info[] = array(
                      "icon" => "icons/16/black/mail.png",
                      "text" => _("Schreiben Sie hier Nachrichten an ".
                                "ausgewählte Empfängerkreise in Stud.IP."));
        $info[] = array(
                      "icon" => "icons/16/black/group2.png",
                      "text" => _("Sie können alle Studiengänge und alle ".
                                "Beschäftigten auswählen, die den ".
                                "Einrichtungen angehören, auf die Sie Zugriff ".
                                "haben."));
        $info[] = array(
                      "icon" => "icons/16/black/edit.png",
                      "text" => sprintf(_("Verwenden Sie im Nachrichteninhalt ".
                                "%sTextformatierungen%s."), 
                                '<a href="'.htmlReady(format_help_url("Basis/VerschiedenesFormat")).
                                '" target="_blank" title="'.
                                _('Stud.IP-Hilfe zu Textformatierungen').'">', '</a>'));
        $infotext = array(
            array("kategorie" => _('Informationen:'),
                  "eintrag" => $info
            )
        );
        $this->infobox = array(
            'content' => $infotext,
            'picture' => 'infobox/messages.jpg'
        );
    }

    public function students_action() {
    }

    public function all_action() {
    }

    public function add_filter_action() {
        if (Request::isXhr()) {
            $this->response->add_header('X-Title', _('Personen filtern'));
            $this->response->add_header('X-No-Buttons', 1);
        }
        if (!$GLOBALS['perm']->have_perm('root')) {
            $this->filterfields = array(
                'RestrictedDegreeFilter' => _('Abschluss'),
                'RestrictedSubjectFilter' => _('Studienfach'),
                'SemesterOfStudyCondition' => _('Fachsemester')
            );
        }
    }

    public function filter_config_action($className) {
        if ($className) {
            $this->field = new $className();
        } else {
            $this->render_nothing();
        }
    }

    public function save_filter_action() {
        CSRFProtection::verifyUnsafeRequest();
        $this->filter = new UserFilter();
        $fields = Request::getArray('field');
        $compareOps = Request::getArray('compare_operator');
        $values = Request::getArray('value');
        $i = 0;
        foreach ($fields as $field) {
            $currentField = new $field();
            $currentField->setCompareOperator($compareOps[$i]);
            $currentField->setValue($values[$i]);
            $this->filter->addField($currentField);
            $i++;
        }
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
