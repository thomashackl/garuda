<?php

class ConfigEnableExport extends Migration
{
    public function up()
    {
        try {
            Config::get()->create('GARUDA_ENABLE_EXPORT', array(
                'value' => 1,
                'type' => 'boolean',
                'range' => 'global',
                'section' => 'garudaplugin',
                'description' => 'Dürfen die Daten der der Empfänger einer Nachricht (inkl. E-Mailadresse) als CSV exportiert werden?'
            ));
        } catch (InvalidArgumentException $e) {}
    }

    public function down()
    {
        $entries = ConfigEntry::findByField('GARUDA_ENABLE_EXPORT');
        foreach ($entries as $e) {
            $e->delete();
        }
    }
}
