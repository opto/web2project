<?php

class bcode extends w2p_Core_BaseObject {
	public $_billingcode_id = null;
	public $company_id;
	public $billingcode_id = null;
	public $billingcode_desc;
	public $billingcode_name;
	public $billingcode_value;
	public $billingcode_status;

	public function __construct() {
		parent::__construct('billingcode', 'billingcode_id');
	}

	public function delete(CAppUI $AppUI) {
        $perms = $AppUI->acl();

        if ($perms->checkModuleItem('system', 'delete')) {
            $q = new w2p_Database_Query();
            $q->addTable('billingcode');
            $q->addUpdate('billingcode_status', '1');
            $q->addWhere('billingcode_id = ' . (int) $this->billingcode_id);

            if (!$q->exec()) {
                return db_error();
            }
            return true;
        }
        return false;
	}

	public function store(CAppUI $AppUI) {
        $perms = $AppUI->acl();
        $stored = false;

        $errorMsgArray = $this->check();

        if (count($errorMsgArray) > 0) {
            return $errorMsgArray;
        }

        if ($perms->checkModuleItem('system', 'edit')) {
            if (($msg = parent::store())) {
                return $msg;
            }
            $stored = true;
        }
        return $stored;
	}

    public function check() {
        // ensure the integrity of some variables
        $errorArray = array();
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        $q = new w2p_Database_Query();
		$q->addQuery('billingcode_id');
		$q->addTable('billingcode');
		$q->addWhere('billingcode_name = \'' . $this->billingcode_name . '\'');
		$q->addWhere('company_id = ' . (int)$this->company_id);

		$found_id = $q->loadResult();
		if ($found_id && $found_id != $this->billingcode_id) {
            $errorArray['billingcode_name'] = $baseErrorMsg . 'code already exists';
        }

        return $errorArray;
    }

    public function getBillingCodes($company_id = -1, $activeOnly = true) {
        $q = new w2p_Database_Query();
        $q->addTable('billingcode', 'bc');
        $q->addQuery('bc.*, c.company_name');
        $q->leftJoin('companies', 'c', 'c.company_id = bc.company_id');
        $q->addOrder('company_name, billingcode_name ASC');
        if ($company_id > -1) {
            $q->addWhere('bc.company_id = ' . (int) $company_id);
        }
        if ($activeOnly) {
            $q->addWhere('billingcode_status = 0');
        }

        return $q->loadHashList('billingcode_id');
    }

    public function calculateTaskCost($task_id) {
        $q = new w2p_Database_Query();
        $q->addTable('task_log', 'tl');
        $q->addQuery('task_log_hours, billingcode_value');
        $q->leftJoin('billingcode', 'bc', 'bc.billingcode_id = tl.task_log_costcode');
        $q->addWhere('tl.task_log_task = '. (int) $task_id);
        $logs = $q->loadList();

        $actualCost = 0;
        $uncountedHours = 0;

        foreach ($logs as $tasklog) {
            if (is_null($tasklog['billingcode_value'])) {
                $uncountedHours += $tasklog['task_log_hours'];
            } else {
                $actualCost += $tasklog['task_log_hours'] * $tasklog['billingcode_value'];
            }
        }

        return array('actualCost' => $actualCost, 'uncountedHours' => $uncountedHours);
    }
}