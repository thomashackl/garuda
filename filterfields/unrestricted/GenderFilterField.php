<?php
 
class GenderFilterField extends UserFilterField
{
    public $userDataDbField = 'geschlecht';
    public $userDataDbTable = 'user_info';
 
    public function __construct($fieldId='')
    {
        $this->validCompareOperators = array(
            '='   => _('ist'),
            '!=' => _('ist nicht'),
        );
 
        $this->validValues = array(
            0 => _('unbekannt'),
            1 => _('männlich'),
            2 => _('weiblich'),
        );
 
        if ($fieldId) {
            $this->id = $fieldId;
            $this->load();
        } else {
            $this->id = $this->generateId();
        }
    }
 
    public function getName()
    {
        return _('Geschlecht');
    }
 
    /**
     * Gets all users with given gender.
     *
     * @return Array All users that are affected by the current condition
     * field.
     */
    public function getUsers($restrictions = array())
    {
        $users = DBManager::get()->fetchFirst("SELECT `user_id` " .
                                              "FROM `" . $this->userDataDbTable . "` " .
                                              "WHERE `" . $this->userDataDbField . "`" . $this->compareOperator .
                                              "?", array($this->value));
       
        return $users;
    }
}
