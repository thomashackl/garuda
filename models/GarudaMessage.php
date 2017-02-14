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
 * @property string send_date database column
 * @property string target database column
 * @property string recipients database column
 * @property string exclude_users database column
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
 * @property GarudaFilter filters has_many GarudaFilter
 * @property Course courses has_many Course
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
            'assoc_foreign_key' => 'message_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        );
        $config['has_and_belongs_to_many']['courses'] = array(
            'class_name' => 'Course',
            'thru_table' => 'garuda_courses',
            'thru_key' => 'message_id',
            'thru_assoc_key' => 'course_id',
            'order_by' => Config::get()->IMPORTANT_SEMNUMBER ?
                'ORDER BY `start_time` DESC, `VeranstaltungsNummer`, `Name`' :
                'ORDER BY `start_time` DESC, `Name`',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );

        parent::configure($config);
    }

    public function __construct($id = null)
    {
        $this->registerCallback('before_store after_store after_initialize', 'cbJsonifyRecipients');
        $this->registerCallback('before_delete', 'cbCleanupFilters');

        parent::__construct($id);
    }

    public function getMessageRecipients()
    {
        $recipients = array();

        // Manually created list of usernames.
        if ($this->target == 'usernames' && count($this->recipients) > 0) {
            $recipients = $this->recipients;

        // Members of selected courses.
        } else if ($this->target == 'courses') {
            $members = array();
            foreach ($this->courses as $course) {
                $members = array_merge($members,
                    array_map(function($m) { return $m->user_id; },
                        CourseMember::findByCourseAndStatus($course->id, array('user', 'autor'))));
            }
            $recipients = array_unique($members);

        // Anything else.
        } else if ($this->target != 'usernames') {
            if (count($this->filters) > 0) {

                UserFilterField::getAvailableFilterFields();

                foreach ($this->filters as $filter) {
                    $f = new UserFilter($filter->filter_id);
                    $recipients = array_merge($recipients, $f->getUsers());
                }

                $recipients = array_unique($recipients);

            } else {
                $recipients = GarudaModel::calculateUsers(
                    $this->author_id, $this->target, GarudaModel::getConfigurationForUser($this->author_id));
            }
        }

        // If there are users to be excluded, remove them now.
        if (count($this->exclude_users) > 0) {
            $recipients = array_diff($recipients, $this->exclude_users);
        }

        return $recipients;
    }

    public function hasMarkers($with_tokens = true)
    {
        $markers = array_map(function ($m) { return '###'.$m->marker.'###'; },
            GarudaMarker::findBySQL($with_tokens ? "1" : "`type` != 'token'"));
        foreach ($markers as $marker) {
            if (strpos($this->message, $marker) !== false) {
                return true;
            }
        }
        return false;
    }

    public function getMarkers($with_tokens = true)
    {
        $found = array();
        $markers = GarudaMarker::findBySQL($with_tokens ? "1" : "`type` != 'token'");
        foreach ($markers as $marker) {
            if (strpos($this->message, $marker->marker) !== false) {
                $found[] = $marker;
            }
        }
        return $found;
    }

    protected function cbJsonifyRecipients($type)
    {
        if ($type === 'before_store') {
            if (!is_string($this->recipients)) {
                $this->recipients = $this->recipients ? json_encode($this->recipients) : null;
            }
            if (!is_string($this->exclude_users)) {
                $this->exclude_users = $this->exclude_users ? json_encode($this->exclude_users) : null;
            }
        }
        if (in_array($type, array('after_initialize', 'after_store'))) {
            if (is_string($this->recipients)) {
                $this->recipients = json_decode($this->recipients, true) ?: array();
            }
            if (is_string($this->exclude_users)) {
                $this->exclude_users = json_decode($this->exclude_users, true) ?: array();
            }
        }
    }

    protected function cbCleanupFilters($event)
    {
        if (count($this->filters) > 0) {
            UserFilterField::getAvailableFilterFields();
            foreach ($this->filters as $filter) {
                $f = new UserFilter($filter->filter_id);
                $f->delete();
            }
        }
    }

}
