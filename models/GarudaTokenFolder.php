<?php

class GarudaTokenFolder extends GarudaFolder {

    /**
     * Retrieves or creates the top folder for a Garuda message or template.
     *
     * Creating top folders for messages is a special task since
     * message attachments can be stored when the message wasn't sent yet.
     * This means that message attachments of an unsent message are stored
     * in a top folder with a range-ID that doesn't belong to a message
     * table entry (yet). Therefore we must create the top folder
     * manually when we can't find the top folder by the method
     * Folder::getTopFolder.
     *
     * @param string $message_id The message-ID of the message whose top folder
     *     shall be returned
     *
     * @return GarudaFolder|null The top folder of the message identified by
     *     $message_id. If the folder can't be retrieved, null is returned.
     */
    public static function findTopFolder($message_id)
    {
        //try to find the top folder:
        $folder = Folder::findOneBySQL(
            "`range_id` = :id AND `folder_type` = :type",
            ['id' => $message_id, 'type' => 'GarudaTokenFolder']
        );

        //check if that was successful:
        if ($folder) {
            return new GarudaTokenFolder($folder);
        } else {
            return self::createTopFolder($message_id);
        }
    }

    /**
     * Creates a root folder (top folder) for a Garuda message or template
     * referenced by its ID.
     *
     * @param string $message_id The ID of a Garuda message or template
     *                           for which a root folder shall be generated.
     *
     * @return GarudaFolder A new GarudaFolder as root folder
     *                      for a message or template.
     */
    public static function createTopFolder($message_id)
    {
        return new GarudaTokenFolder(
            Folder::createTopFolder(
                $message_id,
                'garuda',
                'GarudaTokenFolder'
            )
        );
    }

    /**
     * Returns a localised name of the MessageFolder type.
     */
    public static function getTypeName()
    {
        return dgettext('garudaplugin', 'Ordner für Teilnahmecodes in Nachrichten an Zielgruppen');
    }

}