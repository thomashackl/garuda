<?php
/**
 * overview.php
 *
 * Provides an overview over messages that will be sent in the future
 * and cron settings.
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

class OverviewController extends AuthenticatedController {

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
        Navigation::activateItem('/messaging/garuda/overview');

        // Root can do everything.
        $this->i_am_root = false;
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->i_am_root = true;
        }

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');
        $vw = new ViewsWidget();
        $vw->addLink(dgettext('garuda', 'Vorlagen'), $this->url_for('overview/templates'))
            ->setActive($action == 'templates');
        $vw->addLink(dgettext('garuda', 'Demnächst zu verschicken'), $this->url_for('overview/to_send'))
            ->setActive($action == 'to_send');
        $vw->addLink(dgettext('garuda', 'Geschützt'), $this->url_for('overview/protected'))
            ->setActive($action == 'protected');
        $this->sidebar->addWidget($vw);

    }

    public function index_action()
    {
        $this->relocate('overview/templates');
    }

    public function templates_action()
    {
        PageLayout::setTitle(dgettext('garuda', 'Vorlagen verwalten'));

        $this->templates = GarudaTemplate::findMine();
    }

    /**
     * Overview over messages that will be sent in the future or have been marked as protected.
     */
    public function to_send_action()
    {
        PageLayout::setTitle(dgettext('garuda', 'Demnächst zu verschickende Nachrichten'));

        // Root sees all messages...
        if ($this->i_am_root) {
            // Messages to be sent.
            $query = "`locked` = 0
                AND `done` = 0
                ORDER BY `mkdate` DESC";
            $params = array();

        // ... other users only see their own.
        } else {
            // Messages to be sent.
            $query = "`locked` = 0
                AND `done` = 0
                AND (`author_id` = :me OR `sender_id` = :me)
                ORDER BY `mkdate` DESC";
            $params = array('me' => $GLOBALS['user']->id);

        }
        $this->messages = GarudaMessage::findBySql($query, $params);
    }

    /**
     * Overview of protected messages which will be kept after successful sending.
     */
    public function protected_action()
    {
        PageLayout::setTitle(dgettext('garuda', 'Geschützte Nachrichten'));

        // Root sees all messages...
        if ($this->i_am_root) {
            // Messages to be sent.
            $query = "`locked` = 0
                AND `done` = 1
                AND `protected` = 1
                ORDER BY `mkdate` DESC";
            $params = array();

        // ... other users only see their own.
        } else {
            // Messages to be sent.
            $query = "`locked` = 0
                AND `done` = 0
                AND `protected` = 1
                AND (`author_id` = :me OR `sender_id` = :me)
                ORDER BY `mkdate` DESC";
            $params = array('me' => $GLOBALS['user']->id);

        }
        $this->messages = GarudaMessage::findBySql($query, $params);
    }

    /**
     * Deletes the given message.
     * @param $id the message to delete.
     */
    public function delete_message_action($type = 'message', $id)
    {
        if ($type == 'message') {
            $m = GarudaMessage::find($id);

            if (!$m->done) {
                $target = 'overview/to_send';
            } else if ($m->done && $m->protected) {
                $target = 'overview/protected';
            } else {
                $target = 'overview';
            }
        } else {
            $m = GarudaTemplate::find($id);
            $target = 'overview/templates';
        }

        if (in_array($GLOBALS['user']->id, array($m->author_id, $m->sender_id)) || $GLOBALS['perm']->have_perm('root')) {
            if ($m->delete()) {
                PageLayout::postSuccess($type == 'message' ?
                    dgettext('garuda', 'Die Nachricht wurde gelöscht.') :
                    dgettext('garuda', 'Die Vorlage wurde gelöscht.'));
            } else {
                PageLayout::postError($type == 'message' ?
                    dgettext('garuda', 'Die Nachricht konnte nicht gelöscht werden.') :
                    dgettext('garuda', 'Die Vorlage konnte nicht gelöscht werden.'));
            }
        } else {
            PageLayout::postError($type == 'message' ?
                dgettext('garuda', 'Zugriff verweigert. '.
                    'Sie haben nicht die nötigen Rechte, um diese Nachricht zu löschen.') :
                dgettext('garuda', 'Zugriff verweigert. '.
                    'Sie haben nicht die nötigen Rechte, um diese Vorlage zu löschen.'));
        }
        $this->relocate($target);
    }

    /**
     * Edit a message or template.
     *
     * @param $type one of 'message' or 'template'
     * @param string|null $id edit an already existing entry
     */
    public function edit_message_action($type, $id = '')
    {
        $this->type = $type;
        $this->message = ($type == 'message') ? new GarudaMessage($id) : new GarudaTemplate($id);

        // Process token files if necessary.
        $this->folders = array_map(function ($f) {
                return $f->id;
            },
            Folder::findBySQL("`range_id` = ? AND `folder_type` = 'GarudaTokenFolder'",
                [$id !== '' ? $id : $this->flash['provisional_id']]));

        // Process attachments if necessary.
        if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            $folders = Folder::findBySQL("`range_id` = ? AND `folder_type` = 'GarudaFolder'",
                    [$id !== '' ? $id : $this->flash['provisional_id']]);

            // There are more than one folders -> consolidate.
            $first = null;
            if (count($folders) > 0) {
                foreach ($folders as $folder) {
                    if ($first == null) {
                        $first = $folder;
                    } else {
                        $folder->file_refs->each(function ($f) use ($first) {
                            $f->folder_id = $first->id;
                            $f->store();
                        });
                        $folder->file_refs = [];
                        $folder->delete();
                    }
                }

                $this->message->folders = SimpleCollection::createFromArray([$first]);
                $this->folders[] = $first->id;
            }
        }
    }

    /**
     * Saves a message or template by setting the given data.
     *
     * @param string|null $id already existing message or template.
     */
    public function save_message_action($id = '')
    {
        CSRFProtection::verifyUnsafeRequest();
        switch (Request::option('type')) {
            case 'message':
                break;
            case 'template':
                $t = new GarudaTemplate($id);
                $t->name = Request::get('name');
                if (Request::option('sendto') != 'list') {
                    $t->target = Request::option('sendto');
                } else {
                    $t->target = 'usernames';
                }

                $t->author_id = $GLOBALS['user']->id;
                $t->sender_id = $GLOBALS['user']->id;

                if ($t->target == 'courses' && count(Request::getArray('courses')) > 0) {
                    $t->courses = SimpleORMapCollection::createFromArray(
                        Course::findMany(Request::getArray('courses')));
                }

                if ($t->target == 'usernames') {
                    $t->recipients = array_map(function($u) {
                            return $u->id;
                        }, array_filter(User::findManyByUsername(preg_split("/[\r\n,]+/",
                        Request::get('list'), -1, PREG_SPLIT_NO_EMPTY))));
                }

                if (Request::get('excludelist')) {
                    $t->exclude_users = array_map(function($u) {
                            return $u->id;
                        }, array_filter(User::findManyByUsername(preg_split("/[\r\n,]+/",
                        Request::get('excludelist'), -1, PREG_SPLIT_NO_EMPTY))));
                }

                // Add people getting the message in CC
                if (count(Request::getArray('cc')) > 0) {
                    $t->cc = json_encode(Request::getArray('cc'));
                }

                // Set another sender if root and alternative sender is set, set myself otherwise.
                if ($this->i_am_root) {
                    if (Request::option('sender', 'me') == 'person') {
                        $t->sender_id = Request::option('senderid', $GLOBALS['user']->id);
                    } else if (Request::option('sender', 'me') == 'system') {
                        $t->sender_id = '____%system%____';
                    }
                }

                $t->subject = Request::get('subject');
                $t->message = Request::get('message');

                if ($t->store()) {

                    UserFilterField::getAvailableFilterFields();
                    foreach (ObjectBuilder::buildMany(Request::getArray('filters'), 'UserFilter') as $filter) {
                        $filter->store();
                        $gf = new GarudaFilter();
                        $gf->filter_id = $filter->id;
                        $gf->message_id = $t->id;
                        $gf->user_id = $t->author_id;
                        $gf->store();
                    }

                    if (count(Request::getArray('folders', [])) > 0) {
                        foreach (Folder::findMany(Request::getArray('folders', [])) as $f) {
                            $f->range_id = $t->id;
                            $f->store();
                        }
                    }

                    PageLayout::postSuccess(sprintf(
                        dgettext('garuda', 'Die Vorlage "%s" wurde gespeichert.'),
                        $t->name)
                    );
                } else {
                    PageLayout::postError(sprintf(
                            dgettext('garuda', 'Die Vorlage "%s" konnte nicht gespeichert werden.'),
                            $t->name)
                    );
                }
                $this->relocate('message');
        }
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
}
