<?php
class RecipientsController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        Navigation::activateItem('/messaging/garuda/recipients');
        $this->config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        $this->i_am_root = false;
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->i_am_root = true;
        }
    }

    public function index_action() {
        $info = array();
        $info[] = array(
                        "icon" => "icons/16/black/mail.png",
                        "text" => _("Hier sehen Sie, an welche Empfängerkreise ".
                                "Sie Nachrichten verschicken können."));
        $infotext = array(
            array("kategorie" => _('Informationen:'),
                    "eintrag" => $info
            )
        );
        $this->infobox = array(
            'content' => $infotext,
            'picture' => 'infobox/messages.jpg'
        );
        if (!$this->i_am_root) {
            $this->studycourses = array();
            foreach ($this->config['studycourses'] as $s) {
                if ($this->studycourses[$s['abschluss_id']]) {
                    $this->studycourses[$s['abschluss_id']]['subjects'][$s['studiengang_id']] = $s['subject'];
                } else {
                    $this->studycourses[$s['abschluss_id']] = array(
                        'name' => $s['degree'],
                        'subjects' => array($s['studiengang_id'] => $s['subject'])
                    );
                }
            }
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

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    } 
}
