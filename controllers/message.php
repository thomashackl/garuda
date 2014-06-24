<?php
/**
 * message.php
 *
 * The actual Garuda GUI, used for selecting recipients and writing messages.
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
            PageLayout::addScript($this->plugin->getPluginURL().'/assets/jquery.typing-0.2.0.min.js');
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
        $this->set_content_type('text/html;charset=windows-1252');
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
            if (Request::option('protected')) {
                $this->flash['protected'] = true;
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
            if ($_FILES['tokens']) {
                $filename = $GLOBALS['TMP_PATH'].'/'.uniqid('', true);
                move_uploaded_file($_FILES['tokens']['tmp_name'], $filename);
                $this->flash['token_file'] = $filename;
            }
            if (Request::option('message_tokens')) {
                $this->flash['message_tokens'] = Request::option('message_tokens');
            }
            $this->flash['list'] = Request::get('list');
            $this->flash['subject'] = Request::get('subject');
            $this->flash['message'] = Request::get('message');
            $this->flash['protected'] = Request::option('protected');
            $this->redirect($this->url_for('message/send'));
		// Show normal page.
        } else {
            $this->setInfoBoxImage('infobox/messages.jpg');
            $this->addToInfobox(dgettext('garudaplugin', 'Informationen'),
                                dgettext('garudaplugin', "Schreiben Sie hier Nachrichten an ".
                                    "ausgewählte Empfängerkreise in Stud.IP."),
                                'icons/16/black/mail.png');
            $this->addToInfobox(dgettext('garudaplugin', 'Informationen'),
                                dgettext('garudaplugin', "Sie können alle Studiengänge und alle ".
                                    "Beschäftigten auswählen, die den ".
                                    "Einrichtungen angehören, auf die Sie Zugriff ".
                                    "haben."),
                                'icons/16/black/group2.png');
            $this->addToInfobox(dgettext('garudaplugin', 'Informationen'),
                                sprintf(dgettext('garudaplugin', "Verwenden Sie im Nachrichteninhalt ".
                                    "%sTextformatierungen%s."), 
                                    '<a href="'.htmlReady(format_help_url("Basis/VerschiedenesFormat")).
                                    '" target="_blank" title="'.
                                    dgettext('garudaplugin', 'Stud.IP-Hilfe zu Textformatierungen').'">', '</a>'),
                                'icons/16/black/edit.png');
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
            if ($this->i_am_root) {
                $this->messages = GarudaModel::getMessagesWithTokens();
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
        $error = false;
        UserFilterField::getAvailableFilterFields();
        $users = array();
		if ($this->flash['filters']) {
			// Get configured filters and their corresponding users.
	        foreach ($this->flash['filters'] as $filter) {
	            $f = unserialize($filter);
	            $users = array_merge($users, $f->getUsers());
			}
        } else {
        	if ($GLOBALS['perm']->have_perm('root')) {
        		$c = array();
        	} else {
        		$c = $this->config;
        	}
        	switch ($this->flash['sendto']) {
				case 'all':
					$users = GarudaModel::getAllUsers($GLOBALS['user']->id, $c);
					break;
				case 'students':
					$users = GarudaModel::getAllStudents($GLOBALS['user']->id, $c);
					break;
				case 'employees':
					$users = GarudaModel::getAllEmployees($GLOBALS['user']->id, $c);
					break;
                case 'list':
                    $users = array_map(function($e) {
                        return User::findByUsername($e)->user_id;
                    }, preg_split("/[\r\n,]+/", $this->flash['list'], -1,
                    PREG_SPLIT_NO_EMPTY));
                    break;
        	}
        }

        $users = array_unique($users);

        $tokens = array();
        // Read tokens from an uploaded file.
        if ($this->flash['token_file']) {
            $tokens = GarudaModel::extractTokens($this->flash['token_file']);
            unlink($this->flash['token_file']);
            if (sizeof($tokens) < sizeof($users)) {
                $this->flash['error'] = sprintf(dgettext('garudaplugin',
                    'Es gibt weniger Tokens als Personen für den '.
                    'Nachrichtenempfang!'));
                $error = true;
            }
        }
        // Get tokens that were assigned to a previously sent message.
        if ($this->flash['message_tokens']) {
            $data = GarudaModel::getTokens($this->flash['message_tokens'], true);
            $unassigned = GarudaModel::getUnassignedTokens($this->flash['message_tokens']);
            $unassigned_count = 0;
            foreach ($users as $user) {
                if ($data[$user]) {
                    $tokens[$user] = $data[$user];
                } else {
                    $tokens[$user] = $unassigned[$unassigned_count];
                    $unassigned_count++;
                }
            }
        }

        if (!$error && GarudaCronFunctions::createCronEntry($GLOBALS['user']->id, $users, $this->flash['subject'], $this->flash['message'], $this->flash['protected'], $tokens)) {
            $this->flash['success'] = sprintf(dgettext('garudaplugin',
                'Ihre Nachricht an %s Personen wurde an das System zum Versand '.
                'übergeben.'), sizeof($users));
        } else {
            $this->flash['error'] = sprintf(dgettext('garudaplugin',
                'Ihre Nachricht an %s Personen konnte nicht gesendet werden.'),
                sizeof($users));
        }

        $this->redirect($this->url_for('message'));
    }

    /**
     * Provides a preview of a given text, possibly with Stud.IP formatting
     * in it.
     */
    public function preview_action() {
        $this->text = studip_utf8decode(urldecode(Request::get('text')));
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
