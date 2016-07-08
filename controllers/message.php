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
        SimpleORMap::expireTableScheme();
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        // Check for AJAX.
        if (Request::isXhr()) {
            $this->set_layout(null);
            $request = Request::getInstance();
            foreach ($request as $key => $value) {
                $request[$key] = studip_utf8decode($value);
            }
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
            PageLayout::addScript($this->plugin->getPluginURL().'/assets/jquery.typing-0.2.0.min.js');
        }
        $this->set_content_type('text/html;charset=windows-1252');

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
        $this->relocate('message/write');
    }

    public function write_action($type = 'message', $id = '')
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

        $this->flash['send_date'] = time();
        if (Request::option('send_at_date')) {
            $send_date = date_parse(Request::option('send_date', 'now'));
            if ($send_date >= time()) {
                $this->flash['send_date'] = $send_date;
            }
        }

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
                $this->redirect($this->url_for('userfilter/add', Request::option('sendto')));
            } else {
                $this->redirect($this->url_for('userfilter/addrestricted', Request::option('sendto')));
            }

        // Save the current settings as new template.
        } else if (Request::submitted('save_template')) {

            // Show template edit dialog.
            $this->redirect($this->url_for('overview/edit_message/template'));

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
                dgettext('garudaplugin', "Sie können alle Studiengänge und alle ".
                    "Beschäftigten auswählen, die den ".
                    "Einrichtungen angehören, auf die Sie Zugriff ".
                    "haben."),
                'icons/16/white/group2.png');
            Helpbar::get()->addPlainText(dgettext('garudaplugin', 'Nachrichteninhalt'),
                sprintf(dgettext('garudaplugin', 'Verwenden Sie [Stud.IP-Textformatierungen]%s im '.
                    'Nachrichteninhalt.'),
                    format_help_url('Basis/VerschiedenesFormat')),
                'icons/16/white/edit.png');

            UserFilterField::getAvailableFilterFields();

            $this->filters = array();

            if ($id || $type == 'load') {

                if ($type == 'message') {
                    $this->message = new GarudaMessage($id);
                } else {
                    $this->message = new GarudaTemplate($id ?: Request::int('template'));
                }
                $this->flash['sendto'] = $this->message->target;
                $this->filters = array_map(function ($f) { return new UserFilter($f['filter_id']); },
                    $this->message->filters->toArray());
                // Get alternative sender if applicable.
                if ($this->message->sender_id == '____%system%____') {
                    $this->sender = 'system';
                } else if ($this->message->sender_id != $GLOBALS['user']->id && User::exists($this->message->sender_id)) {
                    $this->sender = 'person';
                    $this->senderid = $this->message->sender_id;
                    $this->user = User::find($this->senderid);
                } else {
                    $this->sender = 'me';
                }

                $this->flash['subject'] = $this->message->subject;
                $this->flash['message'] = $this->message->message;

            } else {

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

            // Show action for loading a message template if applicable.
            if (GarudaTemplate::findByAuthor_id($GLOBALS['user']->id)) {
                $sidebar = Sidebar::get();

                // Groups
                $actions = new ActionsWidget();
                $actions->addLink(_('Aus Vorlage laden'),
                    $this->url_for('message/load_template'),
                    Icon::create('mail+edit', 'clickable'))->asDialog('size=auto');
                $sidebar->addWidget($actions);
            }
        }
    }

    /**
     * Shows a list of available templates for loading.
     */
    public function load_template_action()
    {
        $this->templates = GarudaTemplate::findMine();
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
     * Prepares the message for sending by creating a database entry
     * that will be processed on next cron run.
     */
    public function send_action()
    {
        $error = false;

        $users = array();

        /*
         * Calculate which users this message will be sent to.
         */
        // Message will be sent to people defined by given filters.
        if ($this->flash['filters']) {
            UserFilterField::getAvailableFilterFields();

            // Get configured filters and their corresponding users.
            foreach ($this->flash['filters'] as $filter) {
                $f = unserialize($filter);
                $users = array_merge($users, $f->getUsers());
            }
            $users = array_unique($users);

        // Message will be sent to a pre-defined list of recipient usernames.
        } else if ($this->flash['sendto'] == 'list') {
            $users = array_unique(array_map(function($e) {
                return User::findByUsername($e)->user_id;
            }, preg_split("/[\r\n,]+/", $this->flash['list'], -1,
                PREG_SPLIT_NO_EMPTY)));

        // Whole groups are selected, like "all students".
        } else {
            if ($this->i_am_root) {
                $config = array();
            } else {
                $config = $this->config;
            }
            $users = GarudaModel::calculateUsers($GLOBALS['user']->id, $config);
        }

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

        }

        $message = new GarudaMessage();
        $message->sender_id = $sender;
        $message->author_id = $GLOBALS['user']->id;
        $message->send_date = $this->flash['send_date'] ?: time();

        if ($this->flash['sendto'] == 'list') {
            $message->recipients = $users;
        }
        $message->target = $this->flash['sendto'];

        $message->subject = $this->flash['subject'];
        $message->message = $this->flash['message'];
        $message->protected = (int) $this->flash['protected'];
        $message->attachment_id = $this->flash['attachment_token'];

        if ($message->store()) {

            // Read tokens from an uploaded file.
            if ($this->flash['token_file']) {
                $tokens = GarudaModel::extractTokens($this->flash['token_file']);
                unlink($this->flash['token_file']);
                if (sizeof($tokens) < sizeof($users)) {
                    PageLayout::postError(dgettext('garudaplugin',
                        'Es gibt weniger Tokens als Personen für den ' .
                        'Nachrichtenempfang!'));
                } else {
                    array_walk($tokens, function ($value, $key) use ($message) {
                        $t = new GarudaMessageToken();
                        $t->job_id = $message->id;
                        $t->token = $value;
                        $t->store();
                    });
                }
            }

            // Get tokens that were assigned to a previously sent message.
            if ($this->flash['message_tokens']) {

                // Copy tokens from old message.
                GarudaMessageTokens::copyTokens($this->flash['message_tokens'], $message->id);

                // Get all unassigned tokens so they can be distributed among newly added users.
                $tokens = GarudaMessageToken::findUnassignedTokens($message->id);

                // Find all user_ids who already have a token assigned to them.
                $assigned_users = GarudaMessageToken::findAssignedUser_ids($message->id);

                // Filter the assigned users from the recipients.
                $unassigned_users = array_filter(function ($u) use ($assigned_users) {
                    return in_array($u, $assigned_users);
                }, $assigned_users);

                // Now assign a new token to each of the unassigned users.
                foreach ($unassigned_users as $u) {
                    $token = array_shift($tokens);
                    $token->user_id = $u;
                    $token->store();
                }

            }

            // Process user filters and save them to database.
            if ($this->flash['filters']) {
                foreach ($this->flash['filters'] as $filter) {
                    $f = unserialize($filter);
                    $f->store();
                    $gf = new GarudaFilter();
                    $gf->message_id = $message->id;
                    $gf->type = 'message';
                    $gf->filter_id = $f->id;
                    $gf->store();
                }
            }

            PageLayout::postSuccess(sprintf(
                dgettext('garudaplugin', 'Ihre Nachricht an %u Personen wurde gespeichert.'),
                count($users)));
        } else {
            PageLayout::postSuccess(sprintf(
                dgettext('garudaplugin', 'Ihre Nachricht an %u Personen konnte nicht gespeichert werden.'),
                count($users)));
        }

        $this->relocate('message/write');
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
