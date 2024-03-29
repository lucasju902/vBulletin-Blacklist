<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.11 Patch Level 1 - Licence Number 3578c1c3
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision: 14656 $');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();
$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

$vbulletin->input->clean_array_gpc('r', array(
	'name' => TYPE_STR,
	'dir'  => TYPE_STR
));

$vbulletin->GPC['name'] = preg_replace('#[^a-z0-9_-]#', '', $vbulletin->GPC['name']);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="<?php echo $stylevar['textdirection']; ?>" lang="<?php echo $stylevar['languagecode']; ?>">
<head>
	<title><?php echo $vbulletin->options['bbtitle'] . " - vBulletin $vbphrase[control_panel]"; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $stylevar['charset']; ?>" />
	<link rel="stylesheet" type="text/css" href="../cpstyles/<?php echo $vbulletin->options['cpstylefolder']; ?>/controlpanel.css" />
	<script type="text/javascript" src="../clientscript/vbulletin_global.js"></script>
	<script type="text/javascript">
	<!--
	function js_textarea_send(textarea,doclose)
	{
		opener.document.getElementsByName('<?php echo $vbulletin->GPC['name']; ?>')[0].value = textarea.value;
		if (doclose==1)
		{
			opener.focus();
			self.close();
		}
	}
	// -->
	</script>
</head>
<body onload="self.focus(); fetch_object('popuptextarea').value=opener.document.getElementsByName('<?php echo $vbulletin->GPC['name']; ?>')[0].value;" style="margin:0px">
<form name="popupform" tabindex="1">
<table cellpadding="4" cellspacing="0" border="0" width="100%" height="100%" class="tborder">
<tr>
	<td class="tcat" align="center"><b><?php echo $vbphrase['edit_text']; ?></b></td>
</tr>
<tr>
	<td class="alt1" align="center"><textarea name="popuptextarea" id="popuptextarea" class="code" style="width:95%; height:500px" onkeydown="js_textarea_send(this, 0);" onkeyup="js_textarea_send(this, 0);" dir="<?php echo ($vbulletin->GPC['dir'] ? 'ltr' : 'rtl') ?>"></textarea></td>
</tr>
<tr>
	<td class="tfoot" align="center">
	<input type="button" class="button" value="<?php echo $vbphrase['send']; ?>" onclick="js_textarea_send(this.form.popuptextarea, 1);" accesskey="s" />
	</td>
</tr>
</table>
</form>
</body>
</html>

<?php
/*======================================================================*\
|| ####################################################################
|| # Downloaded: 11:43, Mon Sep 8th 2008
|| # CVS: $RCSfile$ - $Revision: 14656 $
|| ####################################################################
\*======================================================================*/
?>