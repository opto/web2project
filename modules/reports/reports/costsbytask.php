<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

/**
 * Generates a report of the task logs for given dates
 */
$do_report = w2PgetParam($_POST, 'do_report', 0);
$log_pdf = w2PgetParam($_POST, 'log_pdf', 0);

$log_start_date = w2PgetParam($_POST, 'log_start_date', 0);
$log_end_date = w2PgetParam($_POST, 'log_end_date', 0);
$log_all = w2PgetParam($_POST, 'log_all', 0);

// create Date objects from the datetime fields
$start_date = intval($log_start_date) ? new CDate($log_start_date) : new CDate();
$end_date = intval($log_end_date) ? new CDate($log_end_date) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan(new Date_Span('14,0,0,0'));
}
$end_date->setTime(23, 59, 59);
?>
<script language="javascript">
function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'log_' + f_date );
	if (fld_date.value.length>0) {
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
<form name="editFrm" action="index.php?m=reports" method="post" accept-charset="utf-8">
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
    <input type="hidden" name="report_type" value="<?php echo $report_type; ?>" />
    <?php
    if (function_exists('styleRenderBoxTop')) {
        echo styleRenderBoxTop();
    }
    ?>
    <table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period'); ?>:</td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_start_date" id="log_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('to'); ?></td>
            <td nowrap="nowrap">
                <input type="hidden" name="log_end_date" id="log_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                <input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
                    <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                </a>
            </td>
            <td nowrap="nowrap">
                <input type="checkbox" name="log_all" id="log_all" <?php if ($log_all)
            echo 'checked="checked"' ?> />
                <label for="log_all"><?php echo $AppUI->_('Log All'); ?></label>
            </td>
            <td nowrap="nowrap">
                <input type="checkbox" name="log_pdf" id="log_pdf" <?php if ($log_pdf)
            echo 'checked="checked"' ?> />
                <label for="log_pdf"><?php echo $AppUI->_('Make PDF'); ?></label>
            </td>
            <td align="right" width="50%" nowrap="nowrap">
                <input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit'); ?>" />
            </td>
        </tr>
    </table>
</form>

<table width="100%" class="tbl" cellspacing="1" cellpadding="2" border="0">
	<tr>
        <th width="10px" nowrap="true">Work</th>
        <th>Task Name</th>
        <th width="10px" align="center">Task Owner</th>
        <th width="10px" align="center">Start Date</th>
        <th width="10px" align="center">Finish Date</th>
        <th width="10px" align="center">Target Budget</th>
        <th width="10px" align="center">Actual Cost</th>
        <th width="10px" align="center">Diff</th>
        <th width="10px" align="center">% Diff</th>
        <th width="10px" align="center">Daily Budget</th>
        <th width="10px" align="center">Daily Cost</th>
        <th width="10px" align="center">Diff</th>
        <th width="20px" align="center">% Diff</th>
    </tr>
    <?php
    //TODO: rotate the headers by 90 degrees?
    $task = new CTask();
    $taskList = $task->getAllowedTaskList($AppUI, $project_id);
    $bcode = new bcode();

    if (count($taskList)) {
        foreach ($taskList as $taskItem) {
            $task->load($taskItem['task_id']);
            $costs = $bcode->calculateTaskCost($taskItem['task_id'], $start_date, $end_date);
            $tstart = new CDate($task->task_start_date);
            $tend   = new CDate($task->task_end_date);
            $workingDays = $tstart->workingDaysInSpan($tend);
            ?><tr>
                <td><?php echo $task->task_percent_complete; ?>%</td>
                <td><?php echo $task->task_name; ?></td>
                <td align="center"><?php echo CContact::getContactByUserid($task->task_owner); ?></td>
                <td><?php echo $AppUI->formatTZAwareTime($task->task_start_date, $df); ?></td>
                <td><?php echo $AppUI->formatTZAwareTime($task->task_end_date, $df); ?></td>
                <td align="center"><?php echo (int) $task->task_target_budget; ?></td>
                <td align="center"><?php echo (int) $costs['actualCost']; ?></td>
                <td align="center">
                    <?php
                    $diff = (int) ($task->task_target_budget - $costs['actualCost']);
                    echo ($diff < 0) ? '<span style="color: red;">' : '';
                    echo $diff;
                    echo ($diff < 0) ? '<span style="color: red;">' : '';
                    ?>
                </td>
                <td align="center">
                    <?php
                    $perDiff = '-';
                    if ($task->task_target_budget > 0) {
                        $perDiff = 100 * $costs['actualCost'] / $task->task_target_budget;
                        $perDiff = (int) $perDiff.'%';
                    }
                    echo $perDiff;
                    ?>
                </td>
                <td align="center">
                    <?php
                    $dailyBudget = '-';
                    if ($workingDays > 0) {
                        $dailyBudget = (int) ($task->task_target_budget/$workingDays);
                    }
                    echo $dailyBudget;
                    ?>
                </td>
                <td align="center">
                    <?php
                    $dailyCosts = '-';
                    if ($workingDays > 0) {
                        $dailyCosts = (int) ($costs['actualCost']/$workingDays);
                    }
                    echo $dailyCosts;
                    ?>
                </td>
                <td align="center">
                    <?php
                    $diff = (int) ($dailyBudget - $dailyCosts);
                    echo ($diff < 0) ? '<span style="color: red;">' : '';
                    echo $diff;
                    echo ($diff < 0) ? '<span style="color: red;">' : '';
                    ?>
                </td>
                <td align="center">
                    <?php
                    $perDiff = '-';
                    if ($dailyBudget > 0) {
                        $perDiff = 100 * $dailyCosts / $dailyBudget;
                        $perDiff = (int) $perDiff.'%';
                    }
                    echo $perDiff;
                    ?>
                </td>
            </tr><?php
        }
    } else {
        echo '<tr><td colspan="13">'.$AppUI->_('There are no tasks on this project').'</td></tr>';
    }
    ?>
</table>