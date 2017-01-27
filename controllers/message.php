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
            $request = Request::getInstance();
            foreach ($request as $key => $value) {
                $request[$key] = studip_utf8decode($value);
            }
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
            PageLayout::addScript($this->plugin->getPluginURL().'/assets/jquery.typing-0.2.0.min.js');
            PageLayout::addScript($this->plugin->getPluginURL().'/assets/jquery.insert-at-caret.min.js');
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
        PageLayout::setTitle($this->plugin->getDisplayName() .
            ' - ' . dgettext('garudaplugin', 'Nachricht schreiben'));

        // Set values from Request:
        // Message target.
        if (Request::option('sendto')) {
            $this->flash['sendto'] = Request::option('sendto');
        }

        // Prepare course search.
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = array(
                'semtypes' => studygroup_sem_types() ?: array(),
                'exclude' => array()
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => studygroup_sem_types() ?: array(),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => array()
            );

        } else {
            $parameters = array(
                'userid' => $GLOBALS['user']->id,
                'semtypes' => studygroup_sem_types() ?: array(),
                'exclude' => array()
            );
        }
        $coursesearch = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);
        $this->coursesearch = QuickSearch::get('course_id', $coursesearch)
            ->setInputStyle('width:100%')
            ->fireJSFunctionOnSelect('STUDIP.Garuda.addCourse')
            ->render();

        // Keep already selected courses.
        $this->courses = array();
        if (Request::getArray('courses')) {
            $this->flash['courses'] = Request::getArray('courses');
            $this->courses = Course::findMany(Request::getArray('courses'));
        }

        // User filters.
        if (Request::getArray('filters')) {
            $this->flash['filters'] = Request::getArray('filters');
        }

        // Uploaded token file.
        if ($_FILES['tokens']['tmp_name']) {
            $filename = $GLOBALS['TMP_PATH'] . '/' . uniqid('', true);
            move_uploaded_file($_FILES['tokens']['tmp_name'], $filename);
            $this->flash['token_file'] = $filename;
        }
        if (Request::option('message_tokens')) {
            $this->flash['message_tokens'] = Request::option('message_tokens');
        }

        // Manually set list of recipients.
        if (Request::get('list')) {
            $this->flash['list'] = Request::get('list');
        }

        // Exclude users from recipient list.
        if (Request::get('excludelist')) {
            $this->flash['excludelist'] = Request::get('excludelist');
        }

        // Get alternative sender if applicable.
        if ($this->i_am_root && Request::get('sender')) {
            $this->flash['sender'] = Request::option('sender');

            if (Request::option('sender') == 'person' && Request::option('senderid')) {
                $this->flash['senderid'] = Request::option('senderid');
            }
        }

        // Message subject.
        if (Request::get('subject')) {
            $this->flash['subject'] = Request::get('subject');
        }

        // Message text.
        if (Request::get('message')) {
            $this->flash['message'] = Request::get('message');
        }

        // Do not automatically delete message on cleanup run.
        $this->flash['protected'] = Request::option('protected');

        // Optional message attachment.
        $this->flash['attachment_token'] = Request::get('message_id');

        // Alternative date for sending.
        $this->flash['send_date'] = time();
        if (Request::option('send_at_date')) {
            $send_date = strtotime(Request::get('send_date', 'now'));
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

        // Save changes on an existing message or template.
        } else if (Request::submitted('store')) {

            $this->storeMessage(Request::option('id'), Request::option('type'));

            $this->relocate(Request::get('landingpoint'));

        // Export explicit message recipient list
        } else if (Request::submitted('export')) {

            $this->relocate('message/export');

        // Send message to configured recipients.
        } else if (Request::submitted('submit')) {
            CSRFProtection::verifyUnsafeRequest();

            $error = array();
            if (!Request::get('subject')) {
                $error[] = dgettext('garudaplugin', 'Bitte geben Sie einen Betreff an.');
            }
            if (!Request::get('message')) {
                $error[] = dgettext('garudaplugin', 'Bitte geben Sie eine Nachricht an.');
            }
            if (Request::option('sendto') == 'list' && !Request::get('list')) {
                $error[] = dgettext('garudaplugin', 'Bitte geben Sie mindestens einen Nutzernamen an.');
            }
            if (Request::option('sendto') == 'courses' && count(Request::getArray('courses')) == 0) {
                $error[] = dgettext('garudaplugin',
                    'Bitte geben Sie mindestens eine Veranstaltung an, '.
                    'deren Teilnehmende die Nachricht erhalten sollen.');
            }

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
                $users = array_unique(array_filter(array_map(function($e) {
                    return User::findByUsername($e)->user_id;
                }, preg_split("/[\r\n,]+/", $this->flash['list'], -1,
                    PREG_SPLIT_NO_EMPTY))));

            // Message will be sent to members of selected courses.
            } else if ($this->flash['sendto'] == 'courses') {

                $members = array();
                foreach ($this->flash['courses'] as $course) {
                    $members = array_merge($members,
                        array_map(function($m) { return $m->user_id; },
                            CourseMember::findByCourseAndStatus($course, array('user', 'autor'))));
                }
                $users = array_unique($members);

            // Whole groups are selected, like "all students".
            } else {
                if ($this->i_am_root) {
                    $config = array();
                } else {
                    $config = $this->config;
                }
                $users = GarudaModel::calculateUsers($GLOBALS['user']->id, $this->flash['sendto'], $config);
            }

            // If there are users to be excluded, remove them now.
            if ($this->flash['excludelist']) {
                $users = array_diff($users, array_filter(array_map(function($u) {
                    return $u->id;
                }, User::findManyByUsername(preg_split("/[\r\n,]+/",
                    $this->flash['excludelist'], -1, PREG_SPLIT_NO_EMPTY)))));
            }

            if (count($users) < 1) {
                $error[] = dgettext('garudaplugin',
                    'Ihre Nachricht hat aktuell keine Empfänger '.
                    'und kann daher nicht verschickt werden. '.
                    'Bitte verändern Sie die Einstellungen oder '.
                    'speichern Sie die Nachricht als Vorlage ab.');

            } else {
                $this->flash['users'] = $users;
            }

            // Errors found, show corresponding messages.
            if (count($error) > 0) {
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
                    $this->message = GarudaMessage::find($id);
                } else {
                    $this->message = GarudaTemplate::find($id ?: Request::option('template'));
                }

                if ($this->message->target == 'usernames') {
                    $this->flash['sendto'] = 'list';
                    $this->flash['list'] = implode("\n", array_map(function($u) {
                        return $u->username;
                    }, User::findMany($this->message->recipients)));
                } else {
                    $this->flash['sendto'] = $this->message->target;
                }

                if (count($this->message->exclude_users) > 0) {
                    $this->flash['excludelist'] = implode("\n", array_map(function($u) {
                        return $u->username;
                    }, User::findMany($this->message->exclude_users)));
                }

                $this->courses = $this->message->courses;
                $this->filters = array_map(function ($f) { return new UserFilter($f['filter_id']); },
                    $this->message->filters->toArray());
                array_walk($this->filters, function ($f) { $f->show_user_count = true; });
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
                // Groups
                $actions = new ActionsWidget();
                $actions->addLink(_('Aus Vorlage laden'),
                    $this->url_for('message/load_template'),
                    Icon::create('mail+edit', 'clickable'))->asDialog('size=auto');
                $this->sidebar->addWidget($actions);
            }

            $this->markers = GarudaMarker::findBySQL("1 ORDER BY `position`, `marker`");
        }
    }

    /**
     * Shows a list of available templates for loading.
     */
    public function load_template_action()
    {
        $this->templates = GarudaTemplate::findMine();
    }

    public function export_action()
    {
        // Create a new message object with user settings.
        $m = new GarudaMessage();
        $m->target = $this->flash['sendto'];
        if (count($this->flash['filters']) > 0) {
            $m->filters = SimpleORMapCollection::createFromArray(
                array_map(function ($f) { return unserialize($f); },
                    $this->flash['filters']));
        }
        if (count($this->flash['courses']) > 0) {
            $m->courses = SimpleORMapCollection::createFromArray(Course::findMany($this->flash['courses']));
        }
        // Fetch message recipients...
        $recipients = SimpleORMapCollection::createFromArray(
            User::findMany($m->getMessageRecipients()))->orderBy('nachname, vorname, username');

        // ... and create a corresponding csv.
        $data = array();

        switch ($m->target) {
            case 'all':
                $data[] = array(dgettext('garudaplugin', 'Alle Personen'));
                break;
            case 'students':
                $data[] = array(dgettext('garudaplugin', 'Studierende'));
                if (count($m->filters) > 0) {
                    foreach ($m->filters as $f) {
                        $data[] = array($f->toString());
                    }
                }
                break;
            case 'employees':
                $data[] = array(dgettext('garudaplugin', 'Beschäftigte'));
                if (count($m->filters) > 0) {
                    foreach ($m->filters as $f) {
                        $data[] = array($f->toString());
                    }
                }
                break;
            case 'courses':
                $data[] = array(dgettext('garudaplugin', 'Teilnehmende von Veranstaltungen'));
                if (count($m->courses) > 0) {
                    foreach ($m->courses as $c) {
                        $data[] = array($c->getFullname());
                    }
                }
                break;
            case 'list':
                $data[] = array(dgettext('garudaplugin', 'Manuell erstellte Liste von Personen'));
                break;
        }

        $data[] = array(sprintf(dgettext('garudaplugin', 'Daten vom %s'), date('d.m.Y H:i')));
        $data[] = array();

        foreach ($recipients as $r) {
            $data[] = array($r->nachname, $r->vorname, $r->username, $r->email);
        }

        $this->response->add_header('Content-Type', 'text/csv');
        $this->response->add_header('Content-Disposition', 'attachment; filename=nachrichtenempfaenger.csv');
        $this->render_text(array_to_csv($data));
    }

	/**
	 * No filter has been set, show corresponding text.
	 */
    public function sendto_all_action() {
    }

	/**
	 * One or more filters restrict the recipients, show corresponding text.
     * 
     * @param bool $one is only one filter set?
	 */
    public function sendto_filtered_action($one = false) {
        $this->one = $one;
    }

	/**
	 * Recipients are set by course membership, show corresponding text.
     * 
     * @param bool $and do recipients need to be members in all selected courses?
	 */
    public function sendto_courses_action($and = false) {
        $this->and = $and;
    }

    /**
     * Prepares the message for sending by creating a database entry
     * that will be processed on next cron run.
     */
    public function send_action()
    {
        if ($message = $this->storeMessage()) {

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
                GarudaMessageToken::copyTokens($this->flash['message_tokens'], $message->id);

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

            PageLayout::postSuccess(sprintf(
                dgettext('garudaplugin', 'Ihre Nachricht an %u Personen wurde gespeichert.'),
                count($this->flash['users'])));
        } else {
            PageLayout::postSuccess(sprintf(
                dgettext('garudaplugin', 'Ihre Nachricht an %u Personen konnte nicht gespeichert werden.'),
                count($this->flash['users'])));
        }

        $this->relocate('message/write');
    }


    /**
     * Provides a preview of a given text, possibly with Stud.IP formatting
     * in it.
     */
    public function preview_action()
    {
        $this->text = studip_utf8decode(urldecode(Request::get('text')));
    }

    public function marker_info_action($marker_id)
    {
        $marker = GarudaMarker::find($marker_id);
        PageLayout::setTitle(sprintf(
            dgettext('garudaplugin', 'Textersetzung %s'),
            $marker->marker));
        $this->render_text($marker->description);
    }

    // customized #url_for for plugins
    public function url_for($to) {
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

    private function storeMessage($id = '', $type = 'message')
    {
        UserFilterField::getAvailableFilterFields();

        if ($type == 'template') {
            $message = $id ? GarudaTemplate::find($id) : new GarudaTemplate();
        } else {
            $message = $id ? GarudaMessage::find($id) : new GarudaMessage();
        }

        $sender = $GLOBALS['user']->id;

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

        $message->sender_id = $sender;

        if (!$message->author_id) {
            $message->author_id = $GLOBALS['user']->id;
        }

        if ($this->flash['sendto'] == 'list') {
            $message->recipients = array_map(function($u) {
                    return $u->id;
                }, array_filter(User::findManyByUsername(preg_split("/[\r\n,]+/",
                    $this->flash['list'], -1, PREG_SPLIT_NO_EMPTY))));
        }

        if ($this->flash['excludelist']) {
            $message->exclude_users = array_map(function($u) {
                return $u->id;
            }, array_filter(User::findManyByUsername(preg_split("/[\r\n,]+/",
                $this->flash['excludelist'], -1, PREG_SPLIT_NO_EMPTY))));
        }

        $message->target = $this->flash['sendto'] == 'list' ? 'usernames' : $this->flash['sendto'];

        $message->subject = $this->flash['subject'];
        $message->message = $this->flash['message'];

        if ($type == 'message') {
            $message->send_date = $this->flash['send_date'] ?: time();
            $message->protected = $this->flash['protected'] ? 1 : 0;
            $message->attachment_id = $this->flash['attachment_token'];
        }

        if ($this->flash['courses']) {
            $message->courses = SimpleORMapCollection::createFromArray(
                Course::findMany($this->flash['courses']));
        }

        if ($message->store()) {
            $mfilters = GarudaFilter::findByMessage_id($message->id);
            $mfilterIds = array_map(function($f) { return $f->id; }, $mfilters);
            $newFilters = array();

            // Process user filters and save them to database.
            if ($this->flash['filters']) {

                foreach ($this->flash['filters'] as $filter) {
                    $f = unserialize($filter);
                    $f->store();

                    $newFilters[] = $f->id;

                    $gf = new GarudaFilter(array($message->id, $f->id));
                    $gf->user_id = $message->author_id;
                    $gf->store();
                }

            }

            foreach ($mfilters as $filter) {
                if (!in_array($filter->filter_id, $newFilters)) {
                    $uf = new UserFilter($filter->filter_id);
                    $uf->delete();
                    $filter->delete();
                }
            }

            return $message;
        } else {
            return null;
        }
    }

}
