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
 * @category    Stud.IP
 */

require_once(realpath(dirname(__FILE__).'/models/GarudaCronFunctions.php'));
require_once(realpath(dirname(__FILE__).'/models/GarudaModel.php'));

/**
 * Cron job for processing the messages to send.
 */
class GarudaCronjob extends CronJob {

    public static function getName() {
        return dgettext('garudaplugin', 'Nachrichtenversand an Zielgruppen');
    }

    public static function getDescription() {
        return dgettext('garudaplugin', 'Verschickt Nachrichten an gewählte Empfängerkreise.');
    }

    public static function getParameters() {
        return array();
    }

    public function setUp() {

    }

    /**
     * Send all prepared messages.
     */
    public function execute($last_result, $parameters = array()) {
        Log::set('garuda', '/var/log/studip/garuda.log');
        if (!GarudaCronFunctions::cleanup()) {
            Log::error('Could not clean up!');
        }
        $jobs = GarudaCronFunctions::getCronEntries();
        $m = new Message();
        foreach ($jobs as $job) {
            // Mark current entry as locked.
            if (GarudaCronFunctions::lockCronEntry($job['job_id'])) {
                $sender = User::find($job['sender_id']);
                // Get recipients.
                $users = array_map(function($u) {
                    $o = User::find($u);
                    return $o->username;
                }, (array) json_decode($job['recipients']));
                $numRec = sizeof($users);
                /*
                 * Tokens found -> we need to send the messages seperately as
                 * personalized content is included.
                 */
                if ($tokens = GarudaModel::getTokens($job['job_id'], true)) {
                    foreach ($tokens as $user_id => $token) {
                        $u = User::find($user_id);
                        $username = $u->username;
                        // Replace the "###REPLACE###" marker with the actual token.
                        $text = str_replace('###REPLACE###', $token, $job['message']);
                        // Send Stud.IP message with replaced token.
                        $message = $m->send('____%system%____', array($username), $job['subject'], $text);
                    }
                } else {
                    // Send Stud.IP message.
                    $message = $m->send($sender->user_id, $users, $job['subject'], $job['message']);
                }
                // Build full name of the sender for log.
                $senderName = $sender->vorname.' '.$sender->nachname;
                if ($sender->title_front) {
                    $senderName = $sender->title_front.' '.$senderName;
                }
                if ($sender->title_rear) {
                    $senderName .= ', '.$sender->title_rear;
                }
                // Write status to log file.
                if ($message) {
                    Log::info_garuda(sprintf("Message from %s to %s recipients was sent:\n%s\n\n%s", $senderName, $numRec, $job['subject'], $job['message']));
					GarudaCronFunctions::cronEntryDone($job['job_id']);
                } else {
                    Log::error_garuda(sprintf("Message from %s to %s recipients could not be sent:\n%s\n\n%s", $senderName, $numRec, $job['subject'], $job['message']));
                    GarudaCronFunctions::unlockCronEntry($job['job_id']);
                }
            }
        }
    }

    public function tearDown() {

    }
}
