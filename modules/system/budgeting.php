<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$budget_id = (int) w2PgetParam($_GET, 'budget_id', 0);

if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

// get a list of permitted companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => $AppUI->_('None specified')), $companies);

$billingCategory = w2PgetSysVal('BudgetCategory');
$billingCategory = arrayMerge(array('0' => $AppUI->_('None specified')), $billingCategory);

// load the record data
$budget = new budgets();
$budget->load($budget_id);

$titleBlock = new CTitleBlock('Setup Budgets', 'myevo-weather.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();

?>
<script language="javascript" type="text/javascript">
    function setDate( frm_name, f_date ) {
        fld_date = eval( 'document.' + frm_name + '.' + f_date );
        fld_real_date = eval( 'document.' + frm_name + '.' + 'log_' + f_date );
        if (fld_date.value.length > 0) {
            if ((parseDate(fld_date.value))==null) {
                alert('The Date/Time you typed does not match your prefered format, please retype.');
                fld_real_date.value = '';
                fld_date.style.backgroundColor = 'red';
            } else {
                fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
                fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
                fld_date.style.backgroundColor = '';
            }
        } else {
            fld_real_date.value = '';
        }
    }
</script>
<form name="frmAddcode" action="./index.php?m=system" method="post" accept-charset="utf-8">
    <input type="hidden" name="dosql" value="do_budgeting_aed" />
    <input type="hidden" name="del" value="0" />
    <table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
        <tr>
            <th>&nbsp;</th>
            <th><?php echo $AppUI->_('Company'); ?></th>
			<!--<th><?php echo $AppUI->_('Department'); ?></th>-->
            <th align="center"><?php echo $AppUI->_('Start Date'); ?></th>
			<th align="center"><?php echo $AppUI->_('End Date'); ?></th>
            <th><?php echo $AppUI->_('Amount'); ?></th>
            <th><?php echo $AppUI->_('Billing Category'); ?></th>
			<th>&nbsp;</th>
        </tr>
		<tr>
			<td>Add budgeting amount:</td>
			<td align="center">
                <?php
                    echo arraySelect($companies, 'company_id', 'size="1" class="text"', $bcode->company_id, false);
                ?>
			</td>
			<!--<td>TODO: Department</td>-->
            <td align="center">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
            <td align="center">
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
			<td align="center">
				<input type="text" class="text" name="billingcode_value" value="<?php echo $bcode->billingcode_value; ?>" size="10" />
			</td>
			<td align="center">
                <?php
                    echo arraySelect($billingCategory, 'billingcode_category', 'size="1" class="text"', $bcode->billingcode_category, false);
                ?>
			</td>
			<td align="right" width="20">
				<input class="button" type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" />
			</td>
		</tr>
        <?php
		$budgets = $budget->getBudgetAmounts();
        foreach ($budgets as $amounts) {
            ?><tr>
                <td>
                    <a href="?m=system&a=billingcode&billingcode_id=<?php echo $code['billingcode_id']; ?>" title="<?php echo $AppUI->_('edit'); ?>">
                        <img src="<?php echo w2PfindImage('icons/stock_edit-16.png'); ?>" border="0" alt="<?php echo $AppUI->_('edit'); ?>" />
                    </a>
                    <?php if (!$code['billingcode_status']) { ?>
                        <a href="javascript:delIt2(<?php echo $code['billingcode_id']; ?>);" title="<?php echo $AppUI->_('delete'); ?>">
                            <img src="<?php echo w2PfindImage('icons/stock_delete-16.png'); ?>" border="0" alt="<?php echo $AppUI->_('delete'); ?>" />
                        </a>
                    <?php } ?>
                </td>
                <td align="left">&nbsp;<?php echo (('' != $code['company_name']) ? $code['company_name'] : 'None specified'); ?></td>
                <td align="left">&nbsp;<?php echo $code['billingcode_name'] . ($code['billingcode_status'] == 1 ? ' (deleted)' : ''); ?></td>
                <td nowrap="nowrap" align="center"><?php echo $code['billingcode_value']; ?></td>
                <td nowrap="nowrap"><?php echo $code['billingcode_desc']; ?></td>
                <td nowrap="nowrap"><?php echo $billingCategory[$code['billingcode_category']]; ?></td>
            </tr><?php
        } ?>
	</table>
</form>