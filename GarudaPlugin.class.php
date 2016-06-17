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
        parent::__construct();
        // Localization
        bindtextdomain('garudaplugin', realpath(dirname(__FILE__).'/locale'));
        $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'message'));
        $navigation->addSubNavigation('message', new Navigation(dgettext('garudaplugin', 'Nachricht schreiben'), PluginEngine::getURL($this, array(), 'message')));
        $navigation->addSubNavigation('planned', new Navigation(dgettext('garudaplugin', 'Anstehende Nachrichten'), PluginEngine::getURL($this, array(), 'planned')));
        $navigation->addSubNavigation('recipients', new Navigation(dgettext('garudaplugin', 'An wen darf ich schreiben?'), PluginEngine::getURL($this, array(), 'recipients')));
        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation->addSubNavigation('configuration', new Navigation(dgettext('garudaplugin', 'Konfiguration'), PluginEngine::getURL($this, array(), 'configuration')));
        }
        Navigation::addItem('/messaging/garuda', $navigation);
        NotificationCenter::addObserver($this, 'createNavigation', 'NavigationDidActivateItem');
    }

    public function createNavigation() {
        
        /*
         * We only need the plugin if we are in messaging and have at least
         * 'dozent' permissions.
         */
        if (Navigation::hasItem('/messaging/garuda') && Navigation::getItem('/messaging')->isActive() && $GLOBALS['perm']->have_perm('dozent')) {
            $garuda = Navigation::getItem('/messaging/garuda');
            require_once(realpath(dirname(__FILE__).'/models/GarudaModel.php'));
            $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
            if ($config['studycourses'] || $config['institutes']) {
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
        PageLayout::addScript($GLOBALS['ASSETS_URL'].'javascripts/userfilter.js');
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
        StudipAutoloader::addAutoloadPath(realpath(dirname(__FILE__).'/models'));
        StudipAutoloader::addAutoloadPath(realpath(dirname(__FILE__).'/filterfields'));
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
