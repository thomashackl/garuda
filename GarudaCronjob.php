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

require_once(realpath(dirname(__FILE__).'/models/garudamodel.php'));

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
        return array(
            'verbose' => array(
                'type' => 'boolean',
                'default' => false,
                'status' => 'optional',
                'description' => dgettext('garudaplugin', 'Sollen Ausgaben erzeugt werden'),
            ),
        );
    }

    public function setUp() {
        
    }

    /**
     * Send all prepared messages.
     */
    public function execute($last_result, $parameters = array()) {
        Log::set('garuda', $GLOBALS['TMP_PATH'].'/garuda.log');
        $jobs = GarudaModel::getCronEntries();
        foreach ($jobs as $job) {
            // Mark current entry as locked.
            if (GarudaModel::lockCronEntry($job['job_id'])) {
                $sender = new User($job['sender_id']);
                // Get recipients.
                $users = array_map(function($u) {
                    return new User($u);
                }, json_decode($job['recipients']));
                $numRec = sizeof($users);
                // Mail forwarding needs E-Mail address instead of user_id.
                $mailRec = array_map(function($u) {
                    $user = new User($u);
                    return $user->email;
                }, array_unique($users));
                // Send Stud.IP message.
                $m = new Message();
                $message_id = $m->send($sender->user_id, $users, $job['subject'], $job['message']);
                // Build full name of the sender for log.
                $senderName = $sender->vorname.' '.$sender->nachname;
                if ($sender->title_front) {
                    $senderName = $sender->title_front.' '.$senderName;
                }
                if ($sender->title_rear) {
                    $senderName .= ', '.$sender->title_rear;
                }
                // Write status to log file.
                if ($message_id) {
                    Log::info_garuda(sprintf('Message from %s to %s recipients was sent:\n%s\n\n%s', $senderName, $numRec, $job['subject'], $job['message']));
                } else {
                    Log::error_garuda(sprintf('Message from %s to %s recipients could not be sent:\n%s\n\n%s', $senderName, $numRec, $job['subject'], $job['message']));
                }
            }
        }
    }

    public function tearDown() {
        
    }
}