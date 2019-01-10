<?php
/**
 * settings.php
 *
 * Settings for automated message sending, like cron execution period or
 * message cleanup intervals.
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

class SettingsController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args) {
        $GLOBALS['perm']->check('root');
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
        }

        // Navigation handling.
        Navigation::activateItem('/messaging/garuda/settings');

        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/mail-sidebar.png');

    }

    /**
     * Shows current configuration settings for message sending,
     * like cronjob schedule or cleanup interval.
     */
    public function index_action()
    {
        PageLayout::setTitle(dgettext('garudaplugin', 'Einstellungen'));

        $task = CronjobTask::findOneByClass('GarudaCronjob');
        $this->schedule = CronjobSchedule::findOneByTask_id($task->id);

        $this->cleanup = Config::get()->GARUDA_CLEANUP_INTERVAL;
    }

    /**
     * Saves the settings.
     */
    public function save_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $success = true;

        if (Request::int('cleanup') != Config::get()->GARUDA_CLEANUP_INTERVAL) {
            $success = $success && Config::get()->store('GARUDA_CLEANUP_INTERVAL', Request::int('cleanup'));
        }

        if ($success) {
            PageLayout::postSuccess(dgettext('garudaplugin',
                'Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(dgettext('garudaplugin',
                'Die Einstellungen konnten nicht gespeichert werden.'));
        }
        $this->relocate('settings');
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
