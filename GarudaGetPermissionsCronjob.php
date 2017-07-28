<?php
/**
 * GarudaCronjob.class.php
 *
 * Creates and executes cron jobs for sending messages.
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

/**
 * Cron job for refreshing permissions.
 */
class GarudaPermissionsCronjob extends CronJob {

    public static function getName() {
        return dgettext('garudaplugin', 'Nachrichtenversand an Zielgruppen - Rechteaktualisierung');
    }

    public static function getDescription() {
        return dgettext('garudaplugin', 'Aktualisiert die Rechte zum Versand aus einer externen Datenbank.');
    }

    public static function getParameters() {
        return array();
    }

    public function setUp() {

    }

    /**
     * Connect to external DB (if configured), get information which
     * studycourse is assigned to which faculty and update permissions
     * accordingly.
     */
    public function execute($last_result, $parameters = array()) {
        $config = Config::get('GARUDA_PERMISSIONS_EXTERNAL_DB');
        if (count($config) > 0) {

        }
    }

    public function tearDown() {

    }
}
