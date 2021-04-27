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
        return dgettext('garuda', 'Nachrichtenversand an Zielgruppen');
    }

    public static function getDescription() {
        return dgettext('garuda', 'Verschickt Nachrichten an gewählte Empfängerkreise.');
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
        StudipAutoloader::addAutoloadPath(realpath(__DIR__.'/models'));
        StudipAutoloader::addAutoloadPath(realpath(__DIR__ . '/filterfields/unrestricted'));
        StudipAutoloader::addAutoloadPath(realpath(__DIR__ . '/filterfields/restricted'));

        if (!GarudaCronFunctions::cleanup()) {
            echo 'ERROR: Could not clean up!';
        }

        $memory_limit = trim(ini_get('memory_limit'));
        $last = strtolower($memory_limit[strlen($memory_limit)-1]);
        $memory_limit = substr($memory_limit, 0, -1);
        switch($last) {
            case 'g':
                $memory_limit *= 1024;
            case 'm':
                $memory_limit *= 1024;
            case 'k':
                $memory_limit *= 1024;
        }

        if ($memory_limit < 524288000) {
            ini_set('memory_limit', '500M');
        }

        $jobs = GarudaCronFunctions::getCronEntries();
        foreach ($jobs as $job) {
            // Mark current entry as locked.
            if (GarudaCronFunctions::lockCronEntry($job->id)) {

                // Get recipients.
                $recipients = $job->getMessageRecipients();

                $numRec = sizeof($recipients);

                /*
                 * Replaceable markers found -> we need to send the messages separately as
                 * personalized content is included.
                 */
                if ($job->hasMarkers()) {

                    foreach ($recipients as $rec) {
                        $user = User::find($rec);
                        $personalized = GarudaMarker::replaceMarkers($job, $user);
                        $message = $this->send($job->sender_id,
                            array($user->username), $job->subject,
                            $personalized, $job->id);
                    }

                } else {

                    $usernames = DBManager::get()->fetchFirst(
                        "SELECT `username` FROM `auth_user_md5` WHERE `user_id` IN (?)",
                        array($recipients));

                    // Send one Stud.IP message to all recipients at once.
                    $message = $this->send($job->sender_id, $usernames, $job->subject, $job->message, $job->id);
                }

                // Write status to cron log.
                if ($message) {

                    // Finally send message to CC recipients if necessary.
                    if ($job->cc !== null) {
                        $usernames = DBManager::get()->fetchFirst(
                            "SELECT `username` FROM `auth_user_md5` WHERE `user_id` IN (?)",
                            array(json_decode($job->cc)));
                        $this->send($job->sender_id, $usernames, $job->subject, $job->message, $job->id);
                    }

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
                    if ($job->sender_id == '____%system%____') {
                        $senderName = 'Stud.IP';
                    } else {
                        $senderName = $job->sender->getFullname();
                    }
                    echo sprintf("\nERROR: Message from %s to %s recipients could not be sent:\n%s\n\n%s\n",
                        $senderName, $numRec, $job->subject, $job->message);
                    GarudaCronFunctions::unlockCronEntry($job->id);
                }
            } else {
                echo "\nERROR: Cannot lock entry " . $job->id . "\n";
            }
        }
    }

    /**
     * Send a single message (optionally with attachment).
     */
    private function send($sender, $recipients, $subject, $message, $job_id) {
        $mail = new Message();
        $mail->setId($mail->getNewId());

        // Add attachments if necessary
        if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) {
            $folders = Folder::findByRange_id($job_id);

            if (count($folders) > 0) {

                $senderUser = User::find($sender) ?: User::findCurrent();

                // Use or create a message top folder...
                $topFolder = MessageFolder::findTopFolder($mail->id) ?: MessageFolder::createTopFolder($mail->id);

                // ... and add attachments from Garuda job.
                foreach ($folders as $folder) {
                    $folder->file_refs->each(function ($f) use ($senderUser, $topFolder) {
                        $newRef = new FileRef();
                        $newRef->file_id = $f->file_id;
                        $newRef->folder_id = $topFolder->id;
                        $newRef->user_id = $senderUser->id;
                        $newRef->name = $f->name;
                        $newRef->store();
                    });
                }
            }
        }

        $messaging = new messaging();
        $result = $messaging->insert_message(
            $message,
            $recipients,
            $sender,
            '',
            $mail->id,
            '',
            null,
            $subject
        );

        return $result;
    }

    public function tearDown() {

    }
}
