<?php
class UserfilterController extends AuthenticatedController {

    public function before_filter(&$action, &$args) {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (Request::isXhr()) {
            $this->set_layout(null);
            header('Content-Type: text/html; charset=windows-1252');
        } else {
            $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));
            Navigation::activateItem('/messaging/garuda/message');
        }
        $this->filterfields = UserFilterField::getAvailableFilterFields();
    }

    public function add_action($type) {
        $GLOBALS['perm']->check('root');
        if (Request::isXhr()) {
            $this->response->add_header('X-Title', _('Personen filtern'));
            $this->response->add_header('X-No-Buttons', 1);
        }
        switch($type) {
            case 'employees':
                $this->filterfields = array(
                    'InstituteFilterField' => InstituteFilterField::getName(),
                    'StatusgroupFilterField' => StatusgroupFilterField::getName()
                );
                break;
            case 'students':
            default:
                break;
        }
    }

    public function addrestricted_action($type) {
        if (Request::isXhr()) {
            $this->response->add_header('X-Title', _('Personen filtern'));
            $this->response->add_header('X-No-Buttons', 1);
        }
        switch($type) {
            case 'employees':
                $this->filterfields = array(
                    'InstituteFilterField' => array(
                        'depends_on' => '',
                        'instance' => new InstituteFilterField()
                    ),
                    'StatusgroupFilterField' => array(
                        'depends_on' => '',
                        'instance' => new StatusgroupFilterField()
                    )
                );
                break;
            case 'students':
            default:
            $this->filterfields = array(
                'RestrictedDegreeFilterField' => array(
                        'depends_on' => 'RestrictedSubjectFilterField',
                        'instance' => new RestrictedDegreeFilterField(),
                    ),
                'RestrictedSubjectFilterField' => array(
                        'depends_on' => 'RestrictedDegreeFilterField',
                        'instance' => new RestrictedSubjectFilterField()
                    ),
                'SemesterofStudyCondition' => array(
                        'depends_on' => '',
                        'instance' => new SemesterofStudyCondition()
                    )
            );
        }
    }

    public function field_config_action($className) {
        if ($className) {
            $this->field = new $className();
        } else {
            $this->render_nothing();
        }
    }

    public function restricted_field_config_action($className, $restriction, $selectedCompareOp, $selectedValue) {
        if ($restriction == 'all') {
            $restriction = '';
        }
        $this->field = new $className('', $restriction);
        $this->field->setCompareOperator($selectedCompareOp);
        $this->field->setValue($selectedValue);
    }

    public function save_action() {
        CSRFProtection::verifyUnsafeRequest();
        $filter = new UserFilter();
        $fields = Request::getArray('field');
        $compareOps = Request::getArray('compare_operator');
        $values = Request::getArray('value');
        for ($i=0 ; $i < sizeof($fields) ; $i++) {
            $className = $fields[$i];
            if ($className) {
                $currentField = new $className();
                $currentField->setCompareOperator($compareOps[$i]);
                $currentField->setValue($values[$i]);
                $filter->addField($currentField);
            }
        }
        $this->flash['sendto'] = Request::option('sendto');
        if (Request::get('subject')) {
            $this->flash['subject'] = Request::get('subject');
        }
        if (Request::get('message')) {
            $this->flash['message'] = Request::get('message');
        }
        if (Request::getArray('filters')) {
            $filters = Request::getArray('filters');
        } else {
            $filters = array();
        }
        array_push($filters, serialize($filter));
        $this->flash['filters'] = $filters;
        $this->redirect($this->url_for('message'));
    }

    // customized #url_for for plugins
    function url_for($to) {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map("urlencode", $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->plugin, $params, join("/", $args));
    } 
}
