
<?php
/**
 * GarudaFilter.php
 * model class for connecting garuda messages to userfilters.
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
 * @property string job_id database column
 * @property string id alias column for job_id
 * @property string userfilter_id database column
 * @property string mkdate database column
 */
class GarudaFilter extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'garuda_filters';

        parent::configure($config);
    }

}
