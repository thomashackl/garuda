<?php

/**
 * GarudaCronFunctions.php
 *
 * Functions associated with Garuda cron job handling.
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

class GarudaCronFunctions {

    /**
     * Gets all cron entries that are not already locked by a cron instance still running.
     *
     * @return Array of found entries to be processed by cron.
     */
    public static function getCronEntries() {
        return GarudaMessage::findBySQL("`locked` = 0 AND `done` = 0 AND
            (`send_date` IS NULL OR `send_date` <= UNIX_TIMESTAMP())");
    }

    /**
     * Locks the given cron job entry.
     *
     * @param int $entryId entry to be locked
     * @return Successfully locked?
     */
    public static function lockCronEntry($entryId) {
        $m = GarudaMessage::find($entryId);
        $m->locked = 1;
        return $m->store();
    }

    /**
     * unlocks the given cron job entry.
     *
     * @param int $entryId entry to be unlocked
     * @return Successfully unlocked?
     */
    public static function unlockCronEntry($entryId) {
        $m = GarudaMessage::find($entryId);
        $m->locked = 0;
        return $m->store();
    }

    /**
     * Marks the given cron job entry as done.
     *
     * @param int $entryId entry to be locked
     * @return Successfully set?
     */
    public static function cronEntryDone($entryId) {
        $m = GarudaMessage::find($entryId);
        $m->done = 1;
        $m->locked = 0;
        return $m->store();
    }

    /**
     * Deletes already successfully processed cronjobs from database that are
     * older than one week.
     *
     * @return bool Successfully cleaned?
     */
    public static function cleanup() {
        $success = true;

        $jobs = GarudaMessage::findBySQL("`done` = 1 AND `protected` = 0 AND `mkdate` < ?",
            array(time() - (Config::get()->GARUDA_CLEANUP_INTERVAL ?: 7)*24*60*60));

        if ($jobs) {
            foreach ($jobs as $j) {
                $success = $success && GarudaMessage::find($j->id)->delete();
            }
        }
        return $success;
    }

}
