
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
            'foreign_key' => 'user_id'
        );
        $config['has_one']['message'] = array(
            'class_name' => 'GarudaMessage',
            'foreign_key' => 'job_id'
        );

        parent::configure($config);
    }

}
