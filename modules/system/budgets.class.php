<?php
/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

class budgets extends w2p_Core_BaseObject
{
	public $budget_id = 0;
	public $budget_start_date = null;
	public $budget_end_date = null;
	public $budget_amount = 0;
	public $budget_category = '';

	public function __construct() {
		parent::__construct('budgets', 'budget_id');
	}

    public function getBudgetAmounts($company_id = -1, $dept_id = -1) {
        $q = new w2p_Database_Query();
        $q->addTable('budgets', 'b');
        $q->addQuery('b.*, c.company_name');
        $q->leftJoin('companies', 'c', 'c.company_id = b.budget_company');
        $q->addOrder('company_name, budget_start_date ASC');
        if ($company_id > -1) {
            $q->addWhere('b.budget_company = ' . (int) $company_id);
        }
        if ($deptId > -1) {
            $q->addWhere('b.budget_dept = ' . (int) $dept_id);
        }

        return $q->loadHashList('budget_id');
    }

}