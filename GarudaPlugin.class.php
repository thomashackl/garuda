<?php
/**
 * GarudaPlugin.class.php
 *
 * Plugin for sending Stud.IP messages to target audiences at institutes.
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

require 'bootstrap.php';

class GarudaPlugin extends StudIPPlugin implements SystemPlugin {

    /**
     * Create a new Garuda instance initializing navigation and needed scripts.
     */
    public function __construct() {
        StudipAutoloader::addAutoloadPath(realpath(__DIR__.'/models'));

        if (GarudaModel::hasPermission($GLOBALS['user']->id)) {
            parent::__construct();
            // Localization
            bindtextdomain('garuda', realpath(dirname(__FILE__) . '/locale'));
            $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'message'));
            $navigation->addSubNavigation('message',
                new Navigation(dgettext('garudaplugin', 'Nachricht schreiben'),
                    PluginEngine::getURL($this, array(), 'message/write')));
            $navigation->addSubNavigation('overview',
                new Navigation(dgettext('garudaplugin', 'Nachrichtenübersicht'),
                    PluginEngine::getURL($this, array(), 'overview')));
            $navigation->addSubNavigation('recipients',
                new Navigation(dgettext('garudaplugin', 'An wen darf ich schreiben?'),
                    PluginEngine::getURL($this, array(), 'recipients')));
            if ($GLOBALS['perm']->have_perm('root')) {
                $navigation->addSubNavigation('permissions',
                    new Navigation(dgettext('garudaplugin', 'Berechtigungen'),
                        PluginEngine::getURL($this, array(), 'permissions')));
                $navigation->addSubNavigation('settings',
                    new Navigation(dgettext('garudaplugin', 'Einstellungen'),
                        PluginEngine::getURL($this, array(), 'settings')));
            }
            Navigation::addItem('/messaging/garuda', $navigation);
            NotificationCenter::addObserver($this, 'createNavigation', 'NavigationDidActivateItem');
        }
    }

    public function createNavigation() {
        
        /*
         * We only need the plugin if we are in messaging and have at least
         * 'tutor' permissions.
         */
        if (Navigation::hasItem('/messaging/garuda') && Navigation::getItem('/messaging')->isActive() && $GLOBALS['perm']->have_perm('tutor')) {
            $garuda = Navigation::getItem('/messaging/garuda');
            require_once(realpath(dirname(__FILE__).'/models/GarudaModel.php'));
            $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
            if ($config['studycourses'] || $config['institutes'] ||
                    $GLOBALS['perm']->have_perm('root')) {
                Navigation::getItem('/messaging')->addSubNavigation('garuda', $garuda);
            } else {
                Navigation::getItem('/')->removeItem('/messaging/garuda');
            }
        } else {
            Navigation::getItem('/')->removeItem('/messaging/garuda');
        }
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName() {
        return dgettext('garudaplugin', 'Nachrichten an Zielgruppen');
    }

    public function initialize () {
        if (Studip\ENV == 'development') {
            $garudaCSS = $this->getPluginURL().'/assets/garuda.css';
            $garudaJS = $this->getPluginURL().'/assets/garuda.js';
        } else {
            $garudaCSS = $this->getPluginURL().'/assets/garuda.min.css';
            $garudaJS = $this->getPluginURL().'/assets/garuda.min.js';
        }
        PageLayout::addStylesheet($garudaCSS);
        PageLayout::addScript($garudaJS);
    }

    public function perform($unconsumed_path) {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'message'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    private function setupAutoload() {
        StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes/admission');
        StudipAutoloader::addAutoloadPath(realpath(__DIR__ . '/filterfields/unrestricted'));
        StudipAutoloader::addAutoloadPath(realpath(__DIR__ . '/filterfields/restricted'));
    }

    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        require_once(__DIR__.'/GarudaCronjob.php');
        $task = new GarudaCronjob();
        $taskId = CronjobScheduler::getInstance()->registerTask($task, true);
        CronjobScheduler::schedulePeriodic($taskId, -15);
    }

    public static function onDisable($pluginId) {
        $task = CronjobTask::findByClass('GarudaCronjob');
        if ($task) {
            CronjobScheduler::getInstance()->unregisterTask($task[0]->id);
            $task[0]->delete();
        }
        parent::onDisable($pluginId);
    }

}
