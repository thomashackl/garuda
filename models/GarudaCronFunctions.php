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
     * Creates a table entry for the Garuda cronjob containing the desired
     * message and intended recipients.
     *
     * @param String $sender     Who sends this message?
     * @param array  $recipients Intended recipients for this message
     *                           (array of Stud.IP user IDs)
     * @param String $subject    Message subject
     * @param String $message    Message text
     * @param bool   $protected  Protect message from automatic cleanup deletion?
     * @param array  $tokens     Optional token list for text replacing in message
     */
    public static function createCronEntry($sender, &$recipients, $subject, $message, $protected=false, &$tokens=array(), $attachment_id='') {
        $success = true;
        $db = DBManager::get();
        $stmt = $db->prepare("INSERT INTO garuda_messages
            (sender_id, recipients, subject, message, attachment_id, protected, mkdate)
            VALUES
            (:sender, :rec, :subject, :message, :attachment_id, :protected, UNIX_TIMESTAMP())");
        $success = $stmt->execute(array(
            'sender' => $sender,
            'rec' => json_encode($recipients),
            'subject' => $subject,
            'message' => $message,
            'attachment_id' => $attachment_id,
            'protected' => $protected ? 1 : 0)
        );
        if ($success && $tokens) {
            $jobId = $db->lastInsertId();
            $stmt = $db->prepare("INSERT INTO `garuda_tokens` (`job_id`, `user_id`, `token`, `mkdate`) VALUES (?, ?, ?, UNIX_TIMESTAMP())");
            foreach (array_combine((array) $recipients, array_slice($tokens, 0, sizeof((array) $recipients))) as $user => $token) {
                $success = $stmt->execute(array($jobId, $user, $token));
            }
            foreach (array_slice($tokens, sizeof((array) $recipients)) as $free) {
                $success = $success && $stmt->execute(array($jobId, null, $free));
            }
        }
        return $success;
    }

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
        $m->done = 0;
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
