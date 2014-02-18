<?php
class MessageController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
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
        if (Request::submitted('add_filter')) {
            $this->flash['sendto'] = Request::option('sendto');
            if (Request::getArray('filters')) {
                $this->flash['filters'] = Request::getArray('filters');
            }
            if (Request::get('subject')) {
                $this->flash['subject'] = Request::get('subject');
            }
            if (Request::get('message')) {
                $this->flash['message'] = Request::get('message');
            }
            $this->redirect($this->url_for('userfilter/add'));
        } else {
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
            //echo 'Flash:<pre>'.print_r($this->flash, true).'</pre>';
            $this->filters = array();
            foreach ($this->flash['filters'] as $filter) {
                if (preg_match('!!u', $filter)) {
                    $filter = studip_utf8decode($filter);
                }
                $current = unserialize($filter);
                $this->filters[] = $current;
            }
        }
    }

    public function sendto_all_action() {
    }

    public function sendto_filtered_action() {
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

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    } 
}
