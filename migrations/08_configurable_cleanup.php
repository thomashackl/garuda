<?php

class ConfigurableCleanup extends Migration
{
    public function up()
    {
        try {
            Config::get()->create('GARUDA_CLEANUP_INTERVAL', array(
                'value' => 7,
                'type' => 'integer',
                'range' => 'global',
                'section' => 'garudaplugin',
                'description' => 'Wie oft (in Tagen) sollen bereits verschickte Nachrichtenaufträge aus der Datenbank gelöscht werden?'
            ));
        } catch (InvalidArgumentException $e) {}
    }

    public function down()
    {
        $entries = ConfigEntry::findByField('GARUDA_CLEANUP_INTERVAL');
        foreach ($entries as $e) {
            $e->delete();
        }
    }
}
