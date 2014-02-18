<?php
class RecipientsController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        Navigation::activateItem('/messaging/garuda/recipients');
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
        $this->studycourses = array();
        foreach ($this->institutes as $institute) {
            if ($this->config[$institute]['studycourses']) {
                foreach ($this->config[$institute]['studycourses'] as $degree => $professions) {
                    $d = new Degree($degree);
                    $this->studycourses[$degree] = array(
                        'name' => $d->name,
                        'professions' => array()
                    );
                    foreach ($professions as $profession => $assigned) {
                        $p = new StudyCourse($profession);
                        $this->studycourses[$degree]['professions'][$profession] = $p->name;
                    }
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
