<?php
/**
 * repicients.php
 * 
 * Shows which recipients are allowed for me.
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

class RecipientsController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        if (!GarudaModel::hasPermission($GLOBALS['user']->id)) {
            throw new AccessDeniedException(dgettext('garuda',
                'Sie haben nicht die Berechtigung, diese Funktionalität zu nutzen.'));
        }

        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        Navigation::activateItem('/messaging/garuda/recipients');
        $this->config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        $this->i_am_root = false;
        if ($GLOBALS['perm']->have_perm('root')) {
            $this->i_am_root = true;
        }
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');
    }

    public function index_action() {
        PageLayout::setTitle(dgettext('garuda', 'An welche Empfängerkreise darf ich schreiben?'));

        Helpbar::get()->addPlainText(dgettext('garuda', 'Erlaubte Zielgruppen'),
            dgettext('garuda', "Hier sehen Sie, an welche Empfängerkreise Sie Nachrichten verschicken können."),
            Icon::create('mail', 'navigation'));
        if (!$this->i_am_root) {
            $this->studycourses = array();
            foreach ($this->config['studycourses'] as $s) {
                if ($this->studycourses[$s['abschluss_id']]) {
                    $this->studycourses[$s['abschluss_id']]['subjects'][$s['studiengang_id']] = $s['subject'];
                } else {
                    $this->studycourses[$s['abschluss_id']] = array(
                        'name' => $s['degree'],
                        'subjects' => array($s['studiengang_id'] => $s['subject'])
                    );
                }
            }
            $this->institutes = array();
            foreach ($this->config['institutes'] as $id => $data) {
                if (!$this->config['institutes'][$data['faculty']]) {
                    $this->institutes[$id] = $data;
                } else {
                    if ($this->institutes[$id]) {
                        $this->institutes[$id] = $data;
                    } else {
                        if ($id != $data['faculty']) {
                            $this->institutes[$data['faculty']]['sub_institutes'][$id] = $data;
                        }
                    }
                }
            }
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
