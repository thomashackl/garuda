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
        if (!GarudaModel::hasPermission($GLOBALS['user']->id)) {
            throw new AccessDeniedException(dgettext('garuda',
                'Sie haben nicht die Berechtigung, diese Funktionalität zu nutzen.'));
        }

        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        $this->wysiwyg = Config::get()->WYSIWYG && $GLOBALS['user']->cfg->WYSIWYG_DISABLED != 1;

        // Check for AJAX.
        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));

            // We only need these two scripts if there is no CKEditor
            if (!$this->wysiwyg) {
                PageLayout::addScript($this->plugin->getPluginURL() . '/assets/jquery.typing-0.2.0.min.js');
                PageLayout::addScript($this->plugin->getPluginURL() . '/assets/jquery.insert-at-caret.min.js');
            }
        }

        // Navigation handling.
        Navigation::activateItem('/messaging/garuda/message');
        // Get Garuda configuration for my own institutes.
        $institutes = array_map(function($i) { return $i['Institut_id']; }, Institute::getMyInstitutes());
        $this->config = GarudaModel::getConfiguration($institutes);

        // Is the current user allowed to contact selected studycourses?
        $this->allowStudycourses = false;

        foreach ($institutes as $i) {
            if ($GLOBALS['perm']->have_studip_perm($this->config[$i]['min_perm'], $i)) {
                $this->institutes[] = $i;
                if (count($this->config[$i]['studycourses']) > 0) {
                    $this->allowStudycourses = true;
                }
            }
        }
        // Root can do everything.
        $this->i_am_root = false;
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->i_am_root = true;
            $this->allowStudycourses = true;
        }

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');

        UserFilterField::getAvailableFilterFields();
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
        $this->type = $type;

        // Generate (or keep) the provisional ID of an unsaved message.
        // The provisional ID of an opened message is the real message ID.
        $this->provisional_id = $id !== '' ?
            $id :
            (Request::option('provisional_id', '') !== '' ?
                Request::option('provisional_id') :
                md5(uniqid('', true)));
        $this->flash['provisional_id'] = $this->provisional_id;

        // Set values from Request:
        if (Request::option('message_id')) {
            $this->flash['message_id'] = Request::option('message_id');
        }

        // Message target.
        if (Request::option('sendto')) {
            $this->flash['sendto'] = Request::option('sendto');
        }

        // Prepare course search.
        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = array(
                'semtypes' => studygroup_sem_types() ?: array(),
                'exclude' => array(),
                'semesters' => array_map(function ($s) { return $s->semester_id; }, Semester::getAll())
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => studygroup_sem_types() ?: array(),
                'institutes' => array_map(function ($i) {
                    return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => array(),
                'semesters' => array_map(function ($s) { return $s->semester_id; }, Semester::getAll())
            );
        } else {
            $parameters = array(
                'userid' => $GLOBALS['user']->id,
                'semtypes' => studygroup_sem_types() ?: array(),
                'exclude' => array(),
                'semesters' => array_map(function ($s) { return $s->semester_id; }, Semester::getAll())
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

        // Use a token file.
        if (Request::int('use_tokens') === 1) {
            $this->flash['use_tokens'] = true;
        }
        if (Request::option('message_tokens')) {
            $this->flash['message_tokens'] = Request::option('message_tokens');
        }

        // Manually set list of recipients.
        if (Request::get('list')) {
            $this->flash['list'] = Request::get('list');
        }

        // Exclude users from recipient list.
        if (Request::option('exclude')) {
            $this->flash['exclude'] = 'on';
            $this->flash['excludelist'] = Request::get('excludelist');
        }

        // Add other people in CC
        $search = new StandardSearch('user_id');
        $this->ccsearch = QuickSearch::get('cc_user', $search)
            ->setInputStyle('width:100%')
            ->fireJSFunctionOnSelect('STUDIP.Garuda.addCC')
            ->withButton()
            ->render();

        if (count(Request::getArray('cc')) > 0) {
            $this->flash['cc'] = Request::getArray('cc');
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
            $this->flash['message'] = Studip\Markup::purifyHtml(Request::get('message'));
        }

        // Do not automatically delete message on cleanup run.
        $this->flash['protected'] = Request::option('protected');

        // Optional message attachment.
        $this->flash['attachment_token'] = Request::get('message_id');

        // Alternative date for sending.
        $this->flash['send_date'] = time();
        if (Request::option('send_at_date')) {
            $this->flash['send_at_date'] = true;
            $send_date = strtotime(Request::get('send_date', 'now'));
            if ($send_date >= time()) {
                $this->flash['send_date'] = $send_date;
            }
        }

        if (Request::option('type')) {
            $this->flash['type'] = Request::option('type');
        }

        if ($this->i_am_root) {
            $this->messages = GarudaModel::getMessagesWithTokens();

            // Prepare search object for alternative message sender.
            $psearch = new PermissionSearch('user',
                dgettext('garuda', 'Absender suchen'),
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
                $this->redirect($this->url_for('userfilter/add', Request::option('sendto'),
                    Request::option('message_id') == Request::option('provisional_id') ? true : null));
            } else {
                $this->redirect($this->url_for('userfilter/addrestricted', Request::option('sendto'),
                    Request::option('message_id') == Request::option('provisional_id') ? true : null));
            }

        // Save the current settings as new template.
        } else if (Request::submitted('save_template')) {

            // Show template edit dialog.
            $this->redirect($this->url_for('overview/edit_message/template'));

        // Save changes on an existing message or template.
        } else if (Request::submitted('store')) {

            $this->storeMessage(Request::option('message_id'), Request::option('type'));

            $this->relocate(Request::get('landingpoint'));

        // Export explicit message recipient list
        } else if (Request::submitted('export')) {

            $this->relocate('message/export');

        // Send message to configured recipients.
        } else if (Request::submitted('submit')) {
            CSRFProtection::verifyUnsafeRequest();

            $error = array();
            if (!Request::get('subject')) {
                $error[] = dgettext('garuda', 'Bitte geben Sie einen Betreff an.');
            }
            if (!Request::get('message')) {
                $error[] = dgettext('garuda', 'Bitte geben Sie eine Nachricht an.');
            }
            if (Request::option('sendto') == 'list' && !Request::get('list')) {
                $error[] = dgettext('garuda', 'Bitte geben Sie mindestens einen Nutzernamen an.');
            }
            if (Request::option('sendto') == 'courses' && count(Request::getArray('courses')) == 0) {
                $error[] = dgettext('garuda',
                    'Bitte geben Sie mindestens eine Veranstaltung an, '.
                    'deren Teilnehmende die Nachricht erhalten sollen.');
            }

            $users = array();

            /*
             * Calculate which users this message will be sent to.
             */
            // Message will be sent to people defined by given filters.
            if ($this->flash['filters']) {

                // Get configured filters and their corresponding users.
                foreach (ObjectBuilder::buildMany($this->flash['filters'], 'UserFilter') as $filter) {
                    $users = array_merge($users, $filter->getUsers());
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
                    $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
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
                $error[] = dgettext('garuda',
                    'Ihre Nachricht hat aktuell keine Empfänger '.
                    'und kann daher nicht verschickt werden. '.
                    'Bitte verändern Sie die Einstellungen oder '.
                    'speichern Sie die Nachricht als Vorlage ab.');

            } else {
                $this->flash['users'] = $users;
            }

            if ($this->flash['sender'] == 'person' && !$this->flash['senderid']) {
                $error[] = dgettext('garuda',
                    'Sie haben angegeben, dass die Nachricht einen '.
                    'alternativen Absender haben soll, haben aber keine '.
                    'Person als Absender ausgewählt.');
            }

            // Errors found, show corresponding messages.
            if (count($error) > 0) {
                PageLayout::postError(implode('<br>', $error));

                /*
                 * Fill some values as they are needed.
                 */
                $this->filters = $this->flash['filters'] ?
                    ObjectBuilder::buildMany($this->flash['filters'], 'UserFilter') :
                    ($this->message->filters ?
                        array_map(function ($f) { return new UserFilter($f['filter_id']); }, $this->message->filters->toArray()) :
                        []);
                array_walk($this->filters, function ($f) { $f->show_user_count = true; });

                if ($this->flash['cc']) {
                    $this->cc = User::findMany($this->flash['cc'], "ORDER BY `Nachname`, `Vorname`, `username`");
                }

                // Get or cleanup message tokens.
                $this->tokens = $this->getMessageFiles('tokens');

                $this->default_attachments = $this->getMessageFiles('attachments');

                $this->markers = GarudaMarker::findBySQL("1 ORDER BY `position`, `marker`");

            // All okay, continue with message processing.
            } else {
                $this->relocate('message/send');
            }

        // Show normal page.
        } else {

            Helpbar::get()->addPlainText(dgettext('garuda', 'Zielgruppen'),
                dgettext('garuda', "Sie können alle Studiengänge und alle ".
                    "Beschäftigten auswählen, die den ".
                    "Einrichtungen angehören, auf die Sie Zugriff ".
                    "haben."),
                Icon::create('group2'));

            if (!$this->wysiwyg) {
                Helpbar::get()->addPlainText(dgettext('garuda', 'Nachrichteninhalt'),
                    sprintf(dgettext('garuda', 'Verwenden Sie [Stud.IP-Textformatierungen]%s im ' .
                        'Nachrichteninhalt.'),
                        format_help_url('Basis/VerschiedenesFormat')),
                    Icon::create('edit'));
            }

            $this->filters = [];

            if ($id || $type == 'load') {

                if ($type == 'message') {
                    $this->message = GarudaMessage::find($id);
                } else {
                    $this->message = GarudaTemplate::find($id ?: Request::option('template'));
                }

                if ($type == 'template') {
                    $title = sprintf(
                        dgettext('garuda', 'Vorlage "%s" bearbeiten'),
                        $this->message->name
                    );
                } else {
                    $title = dgettext('garuda', 'Nachricht bearbeiten');
                }
                PageLayout::setTitle($title);

                $this->flash['message_id'] = $this->message->id;

                if ($this->message->target == 'usernames') {
                    $this->flash['sendto'] = 'list';
                    $this->flash['list'] = implode("\n", array_map(function($u) {
                        return $u->username;
                    }, User::findMany($this->message->recipients)));
                } else if (!$this->flash['sendto']) {
                    $this->flash['sendto'] = $this->message->target;
                }

                if (is_array($this->message->exclude_users) && count($this->message->exclude_users) > 0) {
                    $this->flash['excludelist'] = implode("\n", array_map(function($u) {
                        return $u->username;
                    }, User::findMany($this->message->exclude_users)));
                }

                if ($this->message->cc !== null) {
                    $this->flash['cc'] = json_decode($this->message->cc);
                }

                $this->courses = $this->message->courses;

                // Create filter objects
                $this->filters = $this->flash['filters'] ?
                    ObjectBuilder::buildMany($this->flash['filters'], 'UserFilter') :
                    array_map(function ($f) { return new UserFilter($f['filter_id']); }, $this->message->filters->toArray());
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

                if ($this->message->send_date > time()) {
                    $this->flash['send_at_date'] = true;
                    $this->flash['send_date'] = $this->message->send_date;
                }

            } else {

                PageLayout::setTitle(dgettext('garuda', 'Nachricht schreiben'));

                if ($this->flash['filters']) {
                    foreach (ObjectBuilder::buildMany($this->flash['filters'], 'UserFilter') as $filter) {
                        $filter->show_user_count = true;
                        $this->filters[] = $filter;
                    }
                }

            }

            if ($this->flash['cc']) {
                $this->cc = User::findMany($this->flash['cc'], "ORDER BY `Nachname`, `Vorname`, `username`");
            }

            // Get or cleanup message tokens.
            $this->tokens = $this->getMessageFiles('tokens');

            $this->default_attachments = $this->getMessageFiles('attachments');

            // Show action for loading a message template if applicable.
            if (GarudaTemplate::findByAuthor_id($GLOBALS['user']->id)) {
                // Groups
                $actions = new ActionsWidget();
                $actions->addLink(dgettext('garuda', 'Aus Vorlage laden'),
                    $this->url_for('message/load_template'),
                    Icon::create('mail+edit', 'clickable'))->asDialog('size=auto');
                $this->sidebar->addWidget($actions);
            }

            $this->markers = GarudaMarker::findBySQL("1 ORDER BY `position`, `marker`");
        }
    }

    /**
     * @param string $type 'attachment' or 'tokens': is the file to upload a
     *                     message attachment or a file with participation tokens?
     */
    public function upload_action($type = 'attachments')
    {
        if ($GLOBALS['user']->id === 'nobody') {
            throw new AccessDeniedException();
        }
        if ($type === 'attachments' && !$GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            throw new AccessDeniedException(dgettext('garuda', 'Mailanhänge sind nicht erlaubt.'));
        }
        $file = $_FILES['file'];
        $output = array(
            'name' => $file['name'],
            'size' => $file['size']
        );
        $file = StandardFile::create($_FILES['file']);
        $message_id = Request::option('message_id');
        $output['message_id'] = $message_id;

        switch ($type) {
            case 'tokens':
                $class = 'GarudaTokenFolder';
                break;
            case 'attachments':
            default:
                $class = 'GarudaFolder';
                break;
        }

        $topFolder = $class::findTopFolder($message_id);
        $uploaded = FileManager::handleFileUpload(
            [
                'tmp_name' => [$file['tmp_name']],
                'name' => [$file['name']],
                'size' => [$file['size']],
                'type' => [$file['type']],
                'error' => [$file['error']]
            ],
            $topFolder,
            $GLOBALS['user']->id
        );
        $error = $topFolder->validateUpload($file, $GLOBALS['user']->id);
        if ($error != null) {
            $this->response->set_status(400);
            $this->render_json(compact('error'));
            return;
        }

        $user = User::findCurrent();

        $file_object = new File();
        $file_object->user_id = $user->id;
        $file_object->mime_type = get_mime_type($output['name']);
        $file_object->name = $output['name'];
        $file_object->size = (int)$output['size'];
        $file_object->author_name = $user->getFullName();

        if ($uploaded['error']) {
            $this->response->set_status(400);
            $error = implode("\n", $uploaded['error']);
            $this->render_json(compact('error'));
            return;
        }

        if (!$uploaded['files'][0] instanceof FileType) {
            $error = dgettext('garuda', 'Ein Systemfehler ist beim Upload aufgetreten.');
            $this->response->set_status(400);
            $this->render_json(compact('error'));
            return;
        }
        $output['document_id'] = $uploaded['files'][0]->getId();

        $output['icon'] = $uploaded['files'][0]->getIcon(Icon::ROLE_CLICKABLE)->asImg(['class' => 'text-bottom']);

        $this->render_json($output);
    }

    public function delete_file_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $file = FileRef::find(Request::option('document_id'));
        if ($file) {

            $folder_id = $file->folder_id;
            $file->delete();

            // Check if corresponding folder is empty and delete it as well.
            if (FileRef::countByFolder_id($folder_id) === 0) {
                Folder::find($folder_id)->delete();
            }
        }
        $this->render_nothing();
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
        $m->author_id = $GLOBALS['user']->id;
        if (is_array($this->flash['filters']) && count($this->flash['filters']) > 0) {
            $filters = ObjectBuilder::buildMany($this->flash['filters'], 'UserFilter');

            $recipients = [];
            foreach ($filters as $f) {
                $recipients = array_merge($recipients, $f->getUsers());
            }

            $recipients = User::findMany($recipients, "ORDER BY `Nachname`, `Vorname`, `username`");
        } else {
            if (is_array($this->flash['courses']) && count($this->flash['courses']) > 0) {
                $m->courses = Course::findMany($this->flash['courses']);
            }
            // Fetch message recipients...
            $recipients = User::findMany($m->getMessageRecipients(), "ORDER BY `nachname`, `vorname`, `username`");
        }

        // ... and create a corresponding csv.
        $data = array();

        switch ($m->target) {
            case 'all':
                $data[] = array(dgettext('garuda', 'Alle Personen'));
                break;
            case 'students':
                $data[] = array(dgettext('garuda', 'Studierende'));
                if (is_array($this->flash['filters']) && count($this->flash['filters']) > 0) {
                    foreach ($filters as $f) {
                        $data[] = array($f->toString());
                    }
                }
                break;
            case 'employees':
                $data[] = array(dgettext('garuda', 'Beschäftigte'));
                if (count($m->filters) > 0) {
                    foreach ($m->filters as $f) {
                        $data[] = array($f->toString());
                    }
                }
                break;
            case 'courses':
                $data[] = array(dgettext('garuda', 'Teilnehmende von Veranstaltungen'));
                if (count($m->courses) > 0) {
                    foreach ($m->courses as $c) {
                        $data[] = array($c->getFullname());
                    }
                }
                break;
            case 'list':
                $data[] = array(dgettext('garuda', 'Manuell erstellte Liste von Personen'));
                break;
        }

        $data[] = array(sprintf(dgettext('garuda', 'Daten vom %s'), date('d.m.Y H:i')));
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

            if ($this->flash['use_tokens']) {
                $this->flash['message_id'] = $message->id;
                $this->flash['provisional_id'] = $message->id;

                /*
                 * Read tokens from an uploaded file.
                 * We could actually handle several uploaded token files here,
                 * but the GUI allows only one file upload at the moment.
                 */
                $tokenFiles = $this->getMessageFiles('tokens');

                if (count($tokenFiles) > 0) {
                    $numRecipients = count($message->getMessageRecipients());

                    $tokens = [];
                    foreach ($tokenFiles as $file) {
                        $tokens = array_merge($tokens, GarudaModel::extractTokens($file['path']));
                    }

                    $tokens = array_unique($tokens);

                    // Assign tokens to job.
                    if (sizeof($tokens) < $numRecipients) {
                        PageLayout::postError(dgettext('garuda',
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
            }

            PageLayout::postSuccess(sprintf(
                dgettext('garuda', 'Ihre Nachricht an %u Personen wurde gespeichert.'),
                count($this->flash['users'])));
        } else {
            PageLayout::postSuccess(sprintf(
                dgettext('garuda', 'Ihre Nachricht an %u Personen konnte nicht gespeichert werden.'),
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
        $this->text = urldecode(Request::get('text'));
    }

    public function marker_info_action($marker_id)
    {
        $marker = GarudaMarker::find($marker_id);
        PageLayout::setTitle(sprintf(
            dgettext('garuda', 'Textersetzung %s'),
            $marker->marker));
        $this->render_text($marker->description);
    }

    // customized #url_for for plugins
    public function url_for($to = '') {
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

        if ($this->flash['cc']) {
            $message->cc = json_encode($this->flash['cc']);
        }

        $message->subject = $this->flash['subject'] ?: '';
        $message->message = $this->flash['message'] ?: '';

        if ($type == 'message') {
            $message->send_date = $this->flash['send_date'] ?: time();
            $message->protected = $this->flash['protected'] ? 1 : 0;
            $message->attachment_id = $this->flash['attachment_token'];
        }

        if ($this->flash['courses']) {
            $message->courses = SimpleORMapCollection::createFromArray(
                Course::findMany($this->flash['courses']));
        }

        if ($message->store() || $message->id) {
            $mfilters = GarudaFilter::findByMessage_id($message->id);
            $mfilterIds = array_map(function($f) { return $f->id; }, $mfilters);
            $newFilters = array();

            // Process user filters and save them to database.
            if ($this->flash['filters']) {

                foreach (ObjectBuilder::buildMany($this->flash['filters'], 'UserFilter') as $filter) {
                    $filter->store();

                    $newFilters[] = $filter->id;

                    $gf = new GarudaFilter(array($message->id, $filter->id));
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

            // Move folders from temporary message id to real ID.
            foreach (Folder::findByRange_id($this->flash['provisional_id']) as $folder) {
                $folder->range_id = $message->id;
                $folder->store();
            }

            return $message;
        } else {
            return null;
        }
    }

    private function getMessageFiles($type = 'attachments')
    {
        $files = [];

        if (($type == 'attachments' && $GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) || $type == 'tokens') {

            // Check if we have loaded a template, then we must get the template files and copy them.
            if ($this->flash['message_id'] != '' && $this->flash['message_id'] != $this->flash['provisional_id']) {
                $id = $this->flash['message_id'];
            } else {
                $id = $this->flash['provisional_id'];
            }

            if ($type == 'attachments') {
                $folderType = 'GarudaFolder';
            } else {
                $folderType = 'GarudaTokenFolder';
            }

            /*
             * Find all files that are in folders that belong to the current message or template.
             * As long as the message is not saved, the ID used here is provisional
             * and does not exist in the database.
             */
            $message_folders = Folder::findBySql(
                "`folder_type` = :type
                        AND `range_type` = 'garuda'
                        AND `user_id` = :user_id
                        AND `range_id` = :range",
                [
                    'type' => $folderType,
                    'user_id' => $GLOBALS['user']->id,
                    'range' => $id
                ]
            );

            $message_files = [];

            /*
             * loop through all found folders, retrieve all file_refs,
             * add them to the files array and store them in a
             * new folder that gets the "provisional" range-ID of this message.
             * After that, delete the old folders.
             */
            foreach ($message_folders as $message_folder) {

                foreach ($message_folder->file_refs as $file_ref) {
                    $message_files[] = $file_ref;

                    if ($this->flash['message_id'] == '' || $this->flash['message_id'] == $this->flash['provisional_id']) {
                        $files[] = [
                            'icon' => Icon::create(
                                FileManager::getIconNameForMimeType(
                                    $file_ref->file->mime_type
                                ),
                                'clickable'
                            )->asImg(['class' => "text-bottom"]),
                            'name' => $file_ref->name,
                            'document_id' => $file_ref->id,
                            'size' => relsize($file_ref->file->size, false),
                            'path' => $file_ref->file->getPath()
                        ];
                    }
                }

            }

            if (count($message_files) > 0) {

                // Create an attachment folder for the new message:
                $new_attachment_folder = $folderType::findTopFolder($id);

                // "bend" the folder-ID of each file to the new attachment folder's ID:
                foreach ($message_files as $file) {
                    $file->folder_id = $new_attachment_folder->getId();
                    $file->store();
                }

                // If we have loaded a template, we need to copy the template folder to the new message.
                if ($this->flash['message_id'] != '' && $this->flash['message_id'] != $this->flash['provisional_id']) {
                    $topFolder = $folderType::findTopFolder($this->flash['provisional_id']);

                    // Copy all files.
                    foreach ($new_attachment_folder->file_refs as $file) {
                        $newRef = FileManager::copyFileRef($file,
                            $topFolder,
                            User::findCurrent());

                        $files[] = [
                            'icon' => Icon::create(
                                FileManager::getIconNameForMimeType(
                                    $newRef->file->mime_type
                                ),
                                'clickable'
                            )->asImg(['class' => "text-bottom"]),
                            'name' => $newRef->name,
                            'document_id' => $newRef->id,
                            'size' => relsize($newRef->file->size, false),
                            'path' => $newRef->file->getPath()
                        ];
                    }
                }
            }

            // Finally cleanup empty or unattached folders.
            $unattached_folders = Folder::findBySql("`folder_type` = :type
                AND `range_type` = 'garuda'
                AND `user_id` = :user_id
                AND `range_id` NOT IN (
                    SELECT `job_id` FROM `garuda_messages` WHERE `user_id` = :user_id
                )
                AND `range_id` NOT IN (
                    SELECT `template_id` FROM `garuda_templates` WHERE `user_id` = :user_id
                )",
                [
                    'type' => $folderType,
                    'user_id' => $GLOBALS['user']->id
                ]
            );

            foreach ($unattached_folders as $unattached_folder) {
                if ($unattached_folder->range_id != $this->flash['provisional_id'] || count($unattached_folder->file_refs) === 0) {
                    $unattached_folder->delete();
                }
            }
        }

        return $files;
    }

}