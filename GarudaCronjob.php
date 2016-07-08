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

require_once(realpath(__DIR__.'/models/GarudaCronFunctions.php'));
require_once(realpath(__DIR__.'/models/GarudaModel.php'));
require_once(realpath(__DIR__.'/models/GarudaFilter.php'));
require_once(realpath(__DIR__.'/models/GarudaMessageToken.php'));
require_once(realpath(__DIR__.'/models/GarudaMessage.php'));

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
        if (!GarudaCronFunctions::cleanup()) {
            echo 'ERROR: Could not clean up!';
        }
        $jobs = GarudaCronFunctions::getCronEntries();
        foreach ($jobs as $job) {
            // Mark current entry as locked.
            if (GarudaCronFunctions::lockCronEntry($job->id)) {

                // Get recipients.
                $recipients = $job->getRecipients();

                // Get recipient usernames.
                $users = array_map(function ($u) {
                    $o = User::find($u);
                    return $o->username;
                }, $recipients);
                $numRec = sizeof($users);

                /*
                 * Tokens found -> we need to send the messages separately as
                 * personalized content is included.
                 */
                if ($job->tokens) {

                    $free_users = array_diff($recipients, GarudaMessageToken::findAssignedUser_ids($job->id));

                    /*
                     * Token assignment and message sending are done via
                     * findAndMapBySQL and not directly on $job->tokens
                     * because there we would get memory problems.
                     */
                    $message = GarudaMessageToken::findAndMapBySQL(function ($token) use (&$free_users, $job) {
                        // Assign token to user if not already assigned.
                        if (!$token->user_id && $user = array_shift($free_users)) {
                            $token->user_id = $user;
                            $token->store();
                        }

                        if ($token->user_id) {
                            // Replace the "###REPLACE###" marker with the actual token.
                            $text = str_replace('###REPLACE###', $token->token, $job->message);

                            // Send Stud.IP message with replaced token.
                            return $this->send($job->sender_id,
                                array($token->user->username), $job->subject,
                                $text, $job->attachment_id);
                        } else {
                            return null;
                        }
                    }, "`job_id` = ?", array($job->id));

                } else {
                    // Send one Stud.IP message to all recipients at once.
                    $message = $this->send($job->sender_id, $users, $job->subject, $job->message,
                        $job->attachment_id);
                }
                // Write status to cron log.
                if ($message) {
                    if ($job->author_id == $job->sender_id) {
                        echo sprintf("\nINFO: Message from %s to %s recipients was sent:\n%s\n\n%s\n",
                            $job->author->getFullname(), $numRec, $job->subject, $job->message);
                    } else {
                        if ($job->sender_id == '____%system%____') {
                            $senderName = 'Stud.IP';
                        } else {
                            $senderName = $job->sender->getFullname();
                        }
                        echo sprintf("\nINFO: Message from %s (sent as %s) to %s recipients was sent:\n%s\n\n%s\n",
                            $job->author->getFullname(), $senderName, $numRec, $job->subject, $job->message);
                    }
					GarudaCronFunctions::cronEntryDone($job->id);
                } else {
                    echo sprintf("\nERROR: Message from %s to %s recipients could not be sent:\n%s\n\n%s\n",
                        $senderName, $numRec, $job->subject, $job->message);
                    GarudaCronFunctions::unlockCronEntry($job->id);
                }
            }
        }
    }

    /**
     * Send a single message (optionally with attachment).
     */
    private function send($sender, $recipients, $subject, $message, $attachment_id) {
        $messaging = new messaging();
        if ($attachment_id) {
            $messaging->provisonal_attachment_id = $attachment_id;
        }
        $result = $messaging->insert_message($message, $recipients, $sender, time(), '', false, '', $subject);
        return $result;      
    }
    
    public function tearDown() {

    }
}
