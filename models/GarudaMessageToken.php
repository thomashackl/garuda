
<?php
/**
 * GarudaMessageToken.php
 * model class for garuda message tokens.
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
 * @property string token_id database column
 * @property string id alias column for token_id
 * @property string job_id database column
 * @property string user_id database column
 * @property string token database column
 * @property string mkdate database column
 * @property User user has_one User
 * @property GarudaMessage message has_one GarudaMessage
 */
class GarudaMessageToken extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'garuda_tokens';
        $config['has_one']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_func' => 'findByUser_id',
        );
        $config['has_one']['message'] = array(
            'class_name' => 'GarudaMessage',
            'foreign_key' => 'job_id'
        );

        parent::configure($config);
    }

    /**
     * Finds the token assigned to the given user and the given job.
     *
     * @param $job_id
     * @param $user_id
     */
    public static function findByJobAndUser($job_id, $user_id)
    {
        return self::findOneBySQL("`job_id` = ? AND `user_id` = ?",
            array($job_id, $user_id));
    }

    /**
     * Finds and returns all tokens belonging to the given job that are not
     * assigned to a user_id.
     *
     * @param int $job_id job to fetch tokens for
     * @param int $number find only specified number of tokens, default unlimited
     */
    public static function findUnassignedTokens($job_id, $number = 0)
    {
        $method = $number == 1 ? 'findOneBySQL' : 'findBySQL';
        return self::$method("`job_id` = ? AND `user_id` IS NULL ORDER BY `token_id`",
            array($job_id));
    }

    /**
     * Fetches all users that have tokens for the given job assigned to them.
     *
     * @param int $job_id job to check token assignments for
     * @return mixed
     */
    public static function findAssignedUser_ids($job_id)
    {
        return DBManager::get()->fetchFirst(
            "SELECT DISTINCT `user_id` FROM `garuda_tokens` WHERE `job_id` = ? AND `user_id` IS NOT NULL",
            array($job_id));
    }

    /**
     * Copies tokens that were used by an already sent message for usage in a new message.
     *
     * @param int $old_job_id already processed and sent message id.
     * @param int $new_job_id new message id to add tokens to.
     */
    public static function copyTokens($old_job_id, $new_job_id)
    {
        DBManager::get()->execute("INSERT INTO `garuda_tokens` (
            SELECT 0, :new_id, `user_id`, `token`, UNIX_TIMESTAMP()
            FROM `garuda_tokens`
            WHERE `job_id` = :old_id)",
            array('new_id' => $new_job_id, 'old_id' => $old_job_id));
    }

}
