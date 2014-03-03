<?php
class MessageController extends AuthenticatedController {

	/**
	 * Actions and settings taking place before every page call.
	 */
    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

		// Check for AJAX.
        if (Request::isXhr()) {
            $this->set_layout(null);
            header('Content-Type: text/html; charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }
		// Navigation handling.
        Navigation::activateItem('/messaging/garuda/message');
		// Get Garuda configuration for my own institutes.
        $institutes = array_map(function($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());
        $this->config = GarudaModel::getConfiguration($institutes);
        foreach ($institutes as $i) {
            if ($GLOBALS['perm']->have_studip_perm($this->config[$i]['min_perm'], $i)) {
                $this->institutes[] = $i;
            }
        }
		// Root can do everything.
        $this->i_am_root = false;
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->i_am_root = true;
        }
    }

	/**
	 * Page for writing new messages and setting recipients.
	 */
    public function index_action() {
		/*
		 * "Add filter" button has been clicked. We need to handle that
		 * action here first, because already set values (e.g. message
		 * subject) shall not be lost.
		 */
        if (Request::submitted('add_filter')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->flash['sendto'] = Request::option('sendto');
			// Get already set filters.
            if (Request::getArray('filters')) {
                $this->flash['filters'] = Request::getArray('filters');
            }
			// Get message subject.
            if (Request::get('subject')) {
                $this->flash['subject'] = Request::get('subject');
            }
			// Get message text.
            if (Request::get('message')) {
                $this->flash['message'] = Request::get('message');
            }
			// Check where to redirect to (root has no restrictions in filters).
            if ($this->i_am_root) {
                $this->redirect($this->url_for('userfilter/add', Request::option('sendto')));
            } else {
                $this->redirect($this->url_for('userfilter/addrestricted', Request::option('sendto')));
            }
		// Send message to configured recipients.
        } else if (Request::submitted('submit')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->flash['sendto'] = Request::option('sendto');
            $this->flash['filters'] = Request::getArray('filters');
            $this->flash['subject'] = Request::get('subject');
            $this->flash['message'] = Request::get('message');
            $this->redirect($this->url_for('message/send'));
		// Show normal page.
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
            UserFilterField::getAvailableFilterFields();
            $this->filters = array();
            if ($this->flash['filters']) {
                foreach ($this->flash['filters'] as $filter) {
                    if (preg_match('!!u', $filter)) {
                        $filter = studip_utf8decode($filter);
                    }
                    $current = unserialize($filter);
                    $this->filters[] = $current;
                }
            }
        }
    }

	/**
	 * No filter has been set, show corresponding text.
	 */
    public function sendto_all_action() {
    }

	/**
	 * One or more filters restrict the recipients, show corresponding text.
	 */
    public function sendto_filtered_action($one=false) {
        $this->one = $one;
    }

	/**
	 * Send the message to the given recipients.
	 */
    public function send_action() {
        $recipients = array();
        UserFilterField::getAvailableFilterFields();
        $users = array();
		// Get configured filters and their corresponding users.
        foreach ($this->flash['filters'] as $filter) {
            $f = unserialize($filter);
            $users = array_merge($users, $f->getUsers());
        }

        $users = array_unique($users);

        if (GarudaModel::createCronEntry($GLOBALS['user']->id, $users, $this->flash['subject'], $this->flash['message'])) {
            $this->flash['success'] = sprintf(_('Ihre Nachricht an %s Personen wurde an das System zum Versand übergeben.'), sizeof($users));
        } else {
            $this->flash['success'] = sprintf(_('Ihre Nachricht an %s Personen konnte nicht gesendet werden.'), sizeof($users));
        }

        $this->redirect($this->url_for('message'));
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
