<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$del = (int) w2PgetParam($_POST, 'del', 0);

$obj = new bcode();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}

$action = ($del) ? 'deleted' : 'stored';
$result = ($del) ? $obj->delete($AppUI) : $obj->store($AppUI);

if (is_array($result)) {
    $AppUI->setMsg($result, UI_MSG_ERROR, true);
    $AppUI->holdObject($obj);
    $AppUI->redirect('m=system&a=billingcode');
}
if ($result) {
    $AppUI->setMsg('Billing Codes '.$action, UI_MSG_OK, true);
    $AppUI->redirect('m=system&a=billingcode');
} else {
    $AppUI->redirect('m=public&a=access_denied');
}

//$obj->_billingcode_id = (int) w2PgetParam($_POST, 'billingcode_id', 0);
//$AppUI->setMsg('Billing Codes');
//if ($del) {
//	if (($msg = $obj->delete())) {
//		$AppUI->setMsg($msg, UI_MSG_ERROR);
//	} else {
//		$AppUI->setMsg('deleted', UI_MSG_ALERT, true);
//	}
//} else {
//	if ($edit) {
//		$obj->_billingcode_id = $edit;
//	}
//	if (($msg = $obj->store())) {
//		$AppUI->setMsg($msg, UI_MSG_ERROR);
//	} else {
//		$AppUI->setMsg('updated', UI_MSG_OK, true);
//	}
//}
//$AppUI->redirect('m=system&a=billingcode');