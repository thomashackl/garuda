<?php
require 'bootstrap.php';

/**
 * GarudaPlugin.class.php
 *
 * ...
 *
 * @author  Thomas Hackl <thomas.hackl@uni-passau.de>
 * @version 1.0
 */

class GarudaPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();
        $navigation = new Navigation($this->getDisplayName(), PluginEngine::getURL($this, array(), 'message'));
        $navigation->addSubNavigation('message', new Navigation(_('Nachricht schreiben'), PluginEngine::getURL($this, array(), 'message')));
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
        if (class_exists("StudipAutoloader")) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
            StudipAutoloader::addAutoloadPath(__DIR__ . '/filterfields');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . '/models/' . $class . '.php';
            });
            spl_autoload_register(function ($class) {
                include_once __DIR__ . '/filterfields/' . $class . '.php';
            });
        }
    }
}
