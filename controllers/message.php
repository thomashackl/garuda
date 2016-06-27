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
 * @category    Garuda
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

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');
    }

	/**
	 * Page for writing new messages and setting recipients.
	 */
    public function index_action()
    {
        // Set values from Request.
        if (Request::option('sendto')) {
            $this->flash['sendto'] = Request::option('sendto');
        }
        if (Request::getArray('filters')) {
            $this->flash['filters'] = Request::getArray('filters');
        }
        if ($_FILES['tokens']['tmp_name']) {
            $filename = $GLOBALS['TMP_PATH'] . '/' . uniqid('', true);
            move_uploaded_file($_FILES['tokens']['tmp_name'], $filename);
            $this->flash['token_file'] = $filename;
        }
        if (Request::option('message_tokens')) {
            $this->flash['message_tokens'] = Request::option('message_tokens');
        }
        if (Request::get('list')) {
            $this->flash['list'] = Request::get('list');
        }
        // Get alternative sender if applicable.
        if ($this->i_am_root && Request::get('sender')) {
            $this->flash['sender'] = Request::option('sender');

            if (Request::option('sender') == 'person' && Request::option('senderid')) {
                $this->flash['senderid'] = Request::option('senderid');
            }
        }
        if (Request::get('subject')) {
            $this->flash['subject'] = Request::get('subject');
        }
        if (Request::get('message')) {
            $this->flash['message'] = Request::get('message');
        }
        $this->flash['protected'] = Request::option('protected');
        $this->flash['attachment_token'] = Request::get('message_id');

        if ($this->i_am_root) {
            $this->messages = GarudaModel::getMessagesWithTokens();

            // Prepare search object for alternative message sender.
            $psearch = new PermissionSearch('user',
                dgettext('garudaplugin', 'Absender suchen'),
                'user_id',
                array(
                    'permission' => array('autor', 'tutor', 'dozent', 'admin', 'root'),
                    'exclude_user' => array()
                )
            );
            $this->fromsearch = QuickSearch::get('fromsearch', $psearch)
                ->fireJSFunctionOnSelect('STUDIP.Garuda.replaceSender')
                ->render();

            if ($this->flash['sender']) {
                $this->sender = $this->flash['sender'];

                if ($this->flash['senderid']) {
                    $this->senderid = $this->flash['senderid'];
                    $this->user = User::find($this->senderid);
                }
            }

        }

		/*
		 * "Add filter" button has been clicked. We need to handle that
		 * action here first, because already set values (e.g. message
		 * subject) shall not be lost.
		 */
        if (Request::submitted('add_filter')) {
            CSRFProtection::verifyUnsafeRequest();

			// Check where to redirect to (root has no restrictions in filters).
            if ($this->i_am_root) {
                $this->relocate('userfilter/add', Request::option('sendto'));
            } else {
                $this->relocate$this->url_for('userfilter/addrestricted', Request::option('sendto'));
            }

		// Send message to configured recipients.
        } else if (Request::submitted('submit')) {
            CSRFProtection::verifyUnsafeRequest();

            // Check for necessary values.
            if (!Request::get('subject') || !Request::get('message') ||
                (Request::option('sendto') == 'list' && !$_FILES['tokens'])) {

                $error = array();
                if (!Request::get('subject')) {
                    $error[] = dgettext('garudaplugin', 'Bitte geben Sie einen Betreff an.');
                }
                if (!Request::get('message')) {
                    $error[] = dgettext('garudaplugin', 'Bitte geben Sie eine Nachricht an.');
                }
                if (Request::option('sendto') == 'list' && !$_FILES['tokens']) {
                    $error[] = dgettext('garudaplugin', 'Bitte geben Sie eine Liste von Nutzernamen an.');
                }

                PageLayout::postError(implode('<br>', $error));
            // All okay, continue with message processing.
            } else {
                $this->relocate('message/send');
            }

		// Show normal page.
        } else {
            Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Zielgruppen'),
                                dgettext('garudaplugin', "Sie k�nnen alle Studieng�nge und alle ".
                                    "Besch�ftigten ausw�hlen, die den ".
                                    "Einrichtungen angeh�ren, auf die Sie Zugriff ".
                                    "haben."),
                                'icons/16/white/group2.png');
            Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Nachrichteninhalt'),
                                sprintf(dgettext('garudaplugin', 'Verwenden Sie [Stud.IP-Textformatierungen]%s im '.
                                    'Nachrichteninhalt.'),
                                    format_help_url('Basis/VerschiedenesFormat')),
                                'icons/16/white/edit.png');
            UserFilterField::getAvailableFilterFields();
            $this->filters = array();
            if ($this->flash['filters']) {
                foreach ($this->flash['filters'] as $filter) {
                    if (preg_match('!!u', $filter)) {
                        $filter = studip_utf8decode($filter);
                    }
                    $current = unserialize($filter);
                    $current->show_user_count = true;
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
        	if ($this->i_am_root) {
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

        $sender = $GLOBALS['user']->id;

        $tokens = array();

        if ($this->i_am_root) {

            // Set alternative sender if applicable.
            switch ($this->flash['sender']) {
                case 'person':
                    $sender = $this->flash['senderid'];
                    break;
                case 'system':
                    $sender = '____%system%____';
                    break;
                case 'me':
                default:
                    break;
            }

            // Read tokens from an uploaded file.
            if ($this->flash['token_file']) {
                $tokens = GarudaModel::extractTokens($this->flash['token_file']);
                unlink($this->flash['token_file']);
                if (sizeof($tokens) < sizeof($users)) {
                    $this->flash['error'] = dgettext('garudaplugin',
                        'Es gibt weniger Tokens als Personen f�r den ' .
                        'Nachrichtenempfang!');
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
                        if ($unassigned[$unassigned_count]) {
                            $tokens[$user] = $unassigned[$unassigned_count];
                            $unassigned_count++;
                        } else {
                            $error = true;
                            $this->flash['error'] = dgettext('garudaplugin',
                                'Es sind zu wenige freie Tokens vorhanden!');
                            break;
                        }
                    }
                }
            }
        }

        if (!$error && GarudaCronFunctions::createCronEntry($sender,
                $users, $this->flash['subject'], $this->flash['message'],
                $this->flash['protected'], $tokens,
                $this->flash['attachment_token'])) {
            $this->flash['success'] = sprintf(dgettext('garudaplugin',
                'Ihre Nachricht an %s Personen wurde an das System zum Versand '.
                '�bergeben.'), sizeof($users));
        } else {
            if (!$this->flash['error']) {
                $this->flash['error'] = sprintf(dgettext('garudaplugin',
                    'Ihre Nachricht an %s Personen konnte nicht gesendet werden.'),
                    sizeof($users));
            }
        }

        $this->relocate('message');
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
