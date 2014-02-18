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

    public function __construct() {
        parent::__construct();
        $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'message'));
        $navigation->addSubNavigation('message', new Navigation(_('Nachricht schreiben'), PluginEngine::getURL($this, array(), 'message')));
        $navigation->addSubNavigation('recipients', new Navigation(_('An wen darf ich schreiben?'), PluginEngine::getURL($this, array(), 'recipients')));
        PageLayout::addScript($GLOBALS['ASSETS_URL'].'javascripts/userfilter.js');
        PageLayout::addScript($this->getPluginURL().'/assets/garuda.js');
        if ($GLOBALS['perm']->have_perm('root')) {
            $navigation->addSubNavigation('configuration', new Navigation(_('Konfiguration'), PluginEngine::getURL($this, array(), 'configuration')));
        }
        Navigation::addItem('/messaging/garuda', $navigation);
    }

    public function getDisplayName() {
        return _('Nachrichten an Zielgruppen');
    }

    public function initialize () {
        PageLayout::addStylesheet($this->getPluginURL().'/assets/garuda.css');
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
}
