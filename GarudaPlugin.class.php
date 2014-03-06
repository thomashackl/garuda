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
 * @category    Stud.IP
 */

require 'bootstrap.php';

class GarudaPlugin extends StudIPPlugin implements SystemPlugin {

    /**
     * Name for cron job.
     */
    const CRON = "GarudaCronjob.php";

    /**
     * Create a new Garuda instance initializing navigation and needed scripts.
     */
    public function __construct() {
        /*
         * We only need the plugin if we are in messaging and have at least
         * 'dozent' permissions.
         */
        if (Navigation::hasItem('/messaging/in') && $GLOBALS['perm']->have_perm('dozent')) {
            require_once(realpath(dirname(__FILE__).'/models/garudamodel.php'));
            $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
            if ($config['studycourses'] || $config['institutes']) {
                parent::__construct();
                $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'message'));
                $navigation->addSubNavigation('message', new Navigation(dgettext('garudaplugin', 'Nachricht schreiben'), PluginEngine::getURL($this, array(), 'message')));
                $navigation->addSubNavigation('recipients', new Navigation(dgettext('garudaplugin', 'An wen darf ich schreiben?'), PluginEngine::getURL($this, array(), 'recipients')));
                if ($GLOBALS['perm']->have_perm('root')) {
                    $navigation->addSubNavigation('configuration', new Navigation(dgettext('garudaplugin', 'Konfiguration'), PluginEngine::getURL($this, array(), 'configuration')));
                }
                Navigation::addItem('/messaging/garuda', $navigation);
            }
        }
    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName() {
        return dgettext('garudaplugin', 'Nachrichten an Zielgruppen');
    }

    public function initialize () {
        PageLayout::addStylesheet($this->getPluginURL().'/assets/garuda.css');
        PageLayout::addScript($GLOBALS['ASSETS_URL'].'javascripts/userfilter.js');
        PageLayout::addScript($this->getPluginURL().'/assets/garuda.js');
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
        StudipAutoloader::addAutoloadPath(__DIR__.'/models');
        StudipAutoloader::addAutoloadPath(__DIR__.'/filterfields');
    }

    public static function onEnable($pluginId) {
        parent::onEnable($pluginId);
        $taskId = CronjobScheduler::registerTask(self::getCronName(), true);
        CronjobScheduler::schedulePeriodic($taskId, -15);
    }

    public static function onDisable($pluginId) {
        $taskId = CronjobTask::findByFilename(self::getCronName());
        CronjobScheduler::unregisterTask($taskId[0]->task_id);
        parent::onDisable($pluginId);
    }

    private static function getCronName() {
        return "public/plugins_packages/intelec/GarudaPlugin/".self::CRON;
        $plugin = PluginEngine::getPlugin(__CLASS__);
        $path = $plugin->getPluginPath();
        return dirname($path)."/".self::CRON;
    }

}
