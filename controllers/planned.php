<?php
/**
 * planned.php
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

class PlannedController extends AuthenticatedController {

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
        Navigation::activateItem('/messaging/garuda/planned');

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
     * Overview over messages that will be sent in the future or have been marked as protected.
     */
    public function index_action() {
        $this->future = GarudaMessage::findBySql("`locked` = 0
            AND `done` = 0 AND (`author_id` = :me OR `sender_id` = :me)
            ORDER BY `mkdate` DESC", array('me' => $GLOBALS['user']->id));
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
                PageLayout::postSuccess(dgettext('garudaplugin', 'Die Nachricht wurde gel�scht.'));
            } else {
                PageLayout::postError(dgettext('garudaplugin', 'Die Nachricht konnte nicht gel�scht werden.'));
            }
        } else {
            PageLayout::postError(dgettext('garudaplugin', 'Zugriff verweigert. '.
                'Sie haben nicht die n�tigen Rechte, um diese Nachricht zu l�schen.'));
        }
        $this->relocate('garuda/planned');
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
