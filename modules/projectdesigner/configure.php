<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/* This file will write a php config file to be included during execution of
* all Project designer files which require the configuration options. */
global $m;

// Deny all but system admins
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$utypes = w2PgetSysVal('UserType');

$CONFIG_FILE = W2P_BASE_DIR . '/modules/projectdesigner/config.php';

$AppUI->savePlace();

//define user type list
$user_types = arrayMerge($utypes, array('9' => $AppUI->_('None')));

$config_options = array('heading1' => $AppUI->_('General Options'), 'show_task_descriptions' => array('description' => $AppUI->_('Show Full Task Description Column'), 'value' => '1', 'type' => 'radio', 'buttons' => array(1 => $AppUI->_('Yes'), 0 => $AppUI->_('No'))), 'chars_task_descriptions' => array('description' => $AppUI->_('Description Length on Tooltip'), 'value' => '200', 'type' => 'text'), );

//if this is a submitted page, overwrite the config file.
if (w2PgetParam($_POST, 'Save', '') != '') {

	if (is_writable($CONFIG_FILE)) {
		if (!$handle = fopen($CONFIG_FILE, 'w')) {
			$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('cannot be opened'), UI_MSG_ERROR);
			exit;
		}

		if (fwrite($handle, "<?php //Do not edit this file by hand, it will be overwritten by the configuration utility. \n") === false) {
			$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('cannot be written to'), UI_MSG_ERROR);
			exit;
		} else {
			foreach ($config_options as $key => $value) {
				if (substr($key, 0, 7) == 'heading')
					continue;

				$val = '';
				switch ($value['type']) {
					case 'checkbox':
						$val = isset($_POST[$key]) ? '1':
						'0';
						break;
					case 'text':
						$val = isset($_POST[$key]) ? $_POST[$key]:
						'';
						break;
					case 'longtext':
						$val = isset($_POST[$key]) ? $_POST[$key]:
						'';
						break;
					case 'select':
						$val = isset($_POST[$key]) ? $_POST[$key]:
						'0';
						break;
					case 'radio':
						$val = $_POST[$key];
						break;
					default:
						break;
				}

				fwrite($handle, "\$PROJDESIGN_CONFIG['" . $key . "'] = '" . $val . "';\n");
			}

			fwrite($handle, "?>\n");
			$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('has been successfully updated'), UI_MSG_OK);
			fclose($handle);
			require ($CONFIG_FILE);
		}
	} else {
		$AppUI->setMsg($CONFIG_FILE . ' ' . $AppUI->_('is not writable'), UI_MSG_ERROR);
	}
} elseif (w2PgetParam($_POST, $AppUI->_('Cancel'), '') != '') {
	$AppUI->redirect('m=system&a=viewmods');
}

//$PROJDESIGN_CONFIG = array();
include ($CONFIG_FILE);

//Read the current config values from the config file and update the array.
foreach ($config_options as $key => $value) {
	if (isset($PROJDESIGN_CONFIG[$key])) {

		$config_options[$key]['value'] = $PROJDESIGN_CONFIG[$key];
	}
}

// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('Project Designer Module Configuration', 'projectdesigner.png', $m,  $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'Modules');
$titleBlock->show();

?>

<form method="post" accept-charset="utf-8">
    <table class="std">
    <?php
    foreach ($config_options as $key => $value) {
    ?>
        <tr>
            <?php
        // the key starts with hr, then just display the value
        if (substr($key, 0, 7) == 'heading') { ?>
              <th align="center" colspan="2"><?php echo $value?></th>
            <?php } else { ?>
            <td align="right"><?php echo $value['description']?></td>
            <td><?php
            switch ($value['type']) {
                case 'checkbox': ?>
              <input type="checkbox" name="<?=$key?>" <?php echo $value['value']?"checked=\"checked\"":""?> />
              <?php
                    break;
                case 'text': ?>
              <input type="text" name="<?=$key?>" style="<?php echo $value['style']?>" value="<?php echo $value['value']?>" />
              <?php
                    break;
                case 'longtext': ?>
              <input type="text" size="70" name="<?=$key?>" style="<?php echo $value['style']?>" value="<?php echo $value['value']?>" />
              <?php
                    break;
                case 'select':
                    print arraySelect($value["list"], $key, 'class="text" size="1" id="' . $key . '" ' . $value["events"], $value["value"]);
                    break;
                case 'radio':
                    foreach ($value['buttons'] as $v => $n) { ?>
                <label><input type="radio" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo $v; ?>" <?php echo (($value['value'] == $v) ? 'checked="checked"' : ''); ?> <?php echo $value['events']; ?> /> <?php echo $n; ?></label>
              <?php }
                    break;
                default:
                    break;
            }
    ?></td>
            <?php
        }
    ?>
        </tr>
    <?php
    }
    ?>
        <tr>
            <td colspan="2" align="right"><input type="Submit" name="Cancel" value="<?php echo $AppUI->_('back')?>" /><input type="Submit" name="Save" value="<?php echo $AppUI->_('save')?>" /></td>
        </tr>
    </table>
</form>