<?php

/**
 * Created by PhpStorm.
 * User: lzw
 * Date: 12/28/16
 * Time: 4:27 AM
 */
class Staffs extends BaseController
{
    /** @var StaffDao */
    public $staffDao;

    function __construct()
    {
        parent::__construct();
        $this->load->model(StaffDao::class);
        $this->staffDao = new StaffDao();
    }

    function create_post()
    {
        $key = $this->post('key');
        if ($key != 'borntobeproud') {
            $this->failure(ERROR_NOT_ALLOW_DO_IT);
            return;
        }
        $user = $this->checkAndGetSessionUser();
        if (!$user) {
            return;
        }
        if ($this->staffDao->isStaff($user->userId)) {
            $this->failure(ERROR_ALREADY_STAFF);
        }
        $ok = $this->staffDao->addStaff($user->userId);
        if (!$ok) {
            $this->failure(ERROR_SQL_WRONG);
            return;
        }
        $this->succeed();
    }

    function list_get()
    {
        $staffs = $this->staffDao->getStaffIds();
        $this->succeed($staffs);
    }
}
