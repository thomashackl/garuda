
<?php
/**
 * GarudaMessage.php
 * model class for garuda messages.
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
 * @property string sender_id database column
 * @property string author_id database column
 * @property string recipients database column
 * @property string subject database column
 * @property string message database column
 * @property string attachment_id database column
 * @property string locked database column
 * @property string done database column
 * @property string protected database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property User author has_one User
 * @property User sender has_one User
 * @property GarudaToken tokens has_many GarudaToken
 */
class GarudaMessage extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'garuda_messages';
        $config['has_one']['author'] = array(
            'class_name' => 'User',
            'assoc_func' => 'findByUser_id',
            'foreign_key' => 'author_id'
        );
        $config['has_one']['sender'] = array(
            'class_name' => 'User',
            'assoc_func' => 'findByUser_id',
            'foreign_key' => 'sender_id'
        );
        $config['has_many']['tokens'] = array(
            'class_name' => 'GarudaMessageToken',
            'foreign_key' => 'job_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        );
        $config['has_many']['filters'] = array(
            'class_name' => 'GarudaFilter',
            'foreign_key' => 'job_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        );

        parent::configure($config);
    }

    public function __construct($id = null)
    {
        $this->registerCallback('before_store after_store after_initialize', 'cbJsonifyRecipients');
        $this->registerCallback('before_delete', 'cbCleanupFilters');

        parent::__construct($id);
    }

    public function getRecipients()
    {
        $recipients = array();

        if ($this->target == 'list' && $this->recipients) {
            $recipients = $this->recipients->pluck('user_id');
        } else if ($this->target != 'list') {
            if ($this->filters) {

                UserFilterField::getAvailableFilterFields();

                foreach ($this->filters as $filter) {
                    $f = new UserFilter($filter->filter_id);
                    $recipients = array_merge($recipients, $f->getUsers());
                }

                $recipients = array_unique($recipients);

            } else {
                $recipients = GarudaModel::calculateUsers($GLOBALS['user']->id, $this->target);
            }
        }

        return $recipients;
    }

    protected function cbJsonifyRecipients($type)
    {
        if ($type === 'before_store' && !is_string($this->recipients)) {
            $this->recipients = json_encode($this->recipients ?: null);
        }
        if (in_array($type, array('after_initialize', 'after_store')) && is_string($this->recipients)) {
            $this->recipients = json_decode($this->recipients, true) ?: array();
        }
    }

    protected function cbCleanupFilters($event)
    {
        if ($this->filters) {
            UserFilterField::getAvailableFilterFields();
            foreach ($this->filters as $filter) {
                $f = new UserFilter($filter->filter_id);
                $f->delete();
            }
        }
    }

}
