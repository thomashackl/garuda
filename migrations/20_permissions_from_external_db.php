<?php

require_once(realpath(__DIR__.'/../GarudaGetPermissionsCronjob.php'));

class PermissionsFromExternalDB extends Migration
{
    public function up()
    {
        Config::get()->create('GARUDA_PERMISSIONS_EXTERNAL_DB_ENABLE', array(
            'value' => '0',
            'type' => 'boolean',
            'range' => 'global',
            'section' => 'garudaplugin',
            'description' =>_('Soll eine externe Datenbank benutzt werden, um die Zuordnungen von Studiengängen zu Einrichtungen auszulesen?')
        ));

        Config::get()->create('GARUDA_PERMISSIONS_EXTERNAL_DB_SETTINGS', array(
            'value' => '[]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'garudaplugin',
            'description' => _('Externe Datenbank mit Informationen zur Zugehörigkeit von Studiengängen zu Einrichtungen')
        ));

        Config::get()->create('GARUDA_PERMISSIONS_EXTERNAL_DB_INSTITUTES', array(
            'value' => '[]',
            'type' => 'array',
            'range' => 'global',
            'section' => 'garudaplugin',
            'description' => _('Zuordnungen von Einrichtungen der externen Datenbank zu Stud.IP-Einrichtungen')
        ));

        GarudaGetPermissionsCronjob::register()->schedulePeriodic(59, 23);
    }

    public function down()
    {
        GarudaGetPermissionsCronjob::unregister();
        Config::get()->delete('GARUDA_PERMISSIONS_EXTERNAL_DB_INSTITUTES');
        Config::get()->delete('GARUDA_PERMISSIONS_EXTERNAL_DB_SETTINGS');
        Config::get()->delete('GARUDA_PERMISSIONS_EXTERNAL_DB_ENABLE');
    }
}
