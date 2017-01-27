
<?php
/**
 * GarudaMarker.php
 * model class for garuda text markers that can be replaced.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Garuda
 *
 * @property string marker_id database column
 * @property string id alias column for marker_id
 * @property string marker database column
 * @property string type database column
 * @property string description database column
 * @property string permission database column
 * @property string replacement database column
 * @property string replacement_female database column
 * @property string replacement_unknown database column
 * @property string position database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class GarudaMarker extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'garuda_markers';

        parent::configure($config);
    }

    public static function replaceMarkers($message, $user)
    {
        $text = self::processText($message->message, $user, $message->getMarkers());
        if (count($message->tokens) > 0) {
            $text = self::processToken($message->id, $text, $user);
        }
        return $text;
    }

    public function getMarkerReplacement($user)
    {
        $replacement = $this->replacement;

        switch ($user->geschlecht) {
            case 0:
                if ($this->replacement_unknown) {
                    $replacement = $this->replacement_unknown;
                }
                break;
            case 2:
                if ($this->replacement_female) {
                    $replacement = $this->replacement_female;
                }
                break;
        }

        switch ($this->type) {
            // Just plain text replacing the marker.
            case 'text':
                $data = words($replacement);
                foreach ($data as $entry) {
                    if (strpos($entry, '###') !== false) {
                        $replacement = str_replace($entry,
                            self::findOneByMarker(str_replace('###', '', $entry))->getMarkerReplacement($user),
                            $replacement);
                    }
                }
                return $replacement;

            // Content from one or more database columns replaces the marker.
            case 'database':
                $data = words($replacement);
                $find = array();
                $replace = array();
                foreach ($data as $entry) {
                    if (strpos($entry, '###') !== false) {
                        $replacement = str_replace($entry,
                            self::findOneByMarker(str_replace('###', '', $entry))->getMarkerReplacement($user),
                            $replacement);
                    } else {
                        // Extract the database fields...
                        list($table, $column) = explode('.', $entry);
                        // ... and query database for values to insert.
                        $stmt = DBManager::get()->prepare("SELECT `:column` FROM `:table` WHERE `user_id` = :userid LIMIT 1");
                        $stmt->bindParam('column', $column, StudipPDO::PARAM_COLUMN);
                        $stmt->bindParam('table', $table, StudipPDO::PARAM_COLUMN);
                        $stmt->bindParam('userid', $user->id);
                        $stmt->execute();
                        $dbdata = $stmt->fetch(PDO::FETCH_ASSOC);
                        $replacement = str_replace($entry, $dbdata[$column], $replacement);
                    }
                }
                // If have empty values from database, there could be excess whitespace -> remove.
                return trim(preg_replace('/(\s)+/', ' ', $replacement));

            // The marker is replaced by the result of a function call.
            case 'function':
                $data = words($replacement);
                $function = array_shift($data);
                return call_user_func_array($function, $data);
        }
    }

    public function getReplacementToken($message_id, $user)
    {
        // Try to find token assigned to given user.
        $token = GarudaMessageToken::findByJobAndUser($message_id, $user->id);

        // No token found -> fetch the next free token and assign it to given user.
        if (!$token) {
            $token = GarudaMessageToken::findUnassignedTokens($message_id, 1);
            $token->user_id = $user->id;
            $token->store();
        }
        return $token->token;
    }

    private static function processText($text, $user, $markers)
    {
        $find = array();
        $replace = array();
        foreach ($markers as $marker) {
            if ($GLOBALS['perm']->have_perm($marker->permission) &&
                    strpos($text, '###' . $marker->marker . '###') !== false &&
                    $marker->type != 'token') {
                $find[] = '###' . $marker->marker . '###';
                $replace[] = $marker->getMarkerReplacement($user);
            }
        }
        $text = str_replace($find, $replace, $text);
        return $text;
    }

    private static function processToken($message_id, $text, $user)
    {
        foreach (self::findByType('token') as $marker) {
            if ($GLOBALS['perm']->have_perm($marker->permission) &&
                    strpos($text, '###' . $marker->marker . '###') !== false) {
                $text = str_replace('###' . $marker->marker . '###',
                    $marker->getReplacementToken($message_id, $user),
                    $text);
            }
        }
        return $text;
    }

}
