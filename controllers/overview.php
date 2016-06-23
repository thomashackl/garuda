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

    protected $utf8decode_xhr = true;

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
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
        $vw->addLink(dgettext('garudaplugin', 'Demnächst zu verschicken'), $this->url_for('overview/to_send'))
            ->setActive($action == 'to_send');
        $vw->addLink(dgettext('garudaplugin', 'In Bearbeitung'), $this->url_for('overview/locked'))
            ->setActive($action == 'locked');
        $vw->addLink(dgettext('garudaplugin', 'Geschützt'), $this->url_for('overview/protected'))
            ->setActive($action == 'protected');
        $this->sidebar->addWidget($vw);

    }

    public function index_action()
    {
        $this->relocate('overview/to_send');
    }

    /**
     * Overview over messages that will be sent in the future or have been marked as protected.
     */
    public function to_send_action()
    {
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
     * Overview of messages that are just processed or are stuck.
     */
    public function locked_action()
    {
        // Root sees all messages...
        if ($this->i_am_root) {
            // Messages to be sent.
            $query = "`locked` = 1
                AND `done` = 0
                ORDER BY `mkdate` DESC";
            $params = array();

            // ... other users only see their own.
        } else {
            // Messages to be sent.
            $query = "`locked` = 1
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
            $query = "`locked` = 1
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
    public function delete_message_action($id)
    {
        $m = GarudaMessage::find($id);
        if ($m->sender_id == $GLOBALS['user']->id || $GLOBALS['perm']->have_perm('root')) {
            if ($m->delete) {
                PageLayout::postSuccess(dgettext('garudaplugin', 'Die Nachricht wurde gelöscht.'));
            } else {
                PageLayout::postError(dgettext('garudaplugin', 'Die Nachricht konnte nicht gelöscht werden.'));
            }
        } else {
            PageLayout::postError(dgettext('garudaplugin', 'Zugriff verweigert. '.
                'Sie haben nicht die nötigen Rechte, um diese Nachricht zu löschen.'));
        }
        $this->relocate('garuda/overview');
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
