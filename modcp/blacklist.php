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
define('CVS_REVISION', '$RCSfile$ - $Revision: 26083 $');
define('NOZIP', 1);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('banning', 'cpuser');
$specialtemplates = array('banemail');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/functions_banning.php');

// ############################# LOG ACTION ###############################
$vbulletin->input->clean_array_gpc('r', array('username' => TYPE_STR));
log_admin_action(!empty($vbulletin->GPC['username']) ? 'username = ' . $vbulletin->GPC['username'] : '');

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

print_cp_header($vbphrase['blacklist']);
$canbanuser = ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] OR can_moderate(0, 'canbanusers')) ? true : false;
$canunbanuser = ($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel'] OR can_moderate(0, 'canunbanusers')) ? true : false;

// check banning permissions
if (!$canbanuser AND !$canunbanuser)
{
	print_stop_message('no_permission_ban_users');
}

// set default action
if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'modify';
}

// #############################################################################
// lift a ban

if ($_REQUEST['do'] == 'liftban')
{
	$vbulletin->input->clean_array_gpc('g', array(
		'blacklistid' => TYPE_INT
	));

	if (!$canunbanuser)
	{
		print_stop_message('no_permission_un_blacklist_users');
	}
	$email = $db->query_first("
		SELECT *
		FROM " . TABLE_PREFIX . "blacklist
		WHERE blacklist.blacklistid = " . $vbulletin->GPC['blacklistid'] );

	// check we got a record back and that the returned user is in a banned group
	if (!$email)
	{
		print_stop_message('invalid_email_specified');
	}

	// show confirmation message
	print_form_header('blacklist', 'doliftban');
	construct_hidden_code('blacklistid', $vbulletin->GPC['blacklistid']);
	print_table_header($vbphrase['remove_blacklist']);
	print_description_row(construct_phrase($vbphrase['confirm_remove_blacklist_on_x'], $email['email']));
	print_submit_row($vbphrase['yes'], '', 2, $vbphrase['no']);

}

if ($_POST['do'] == 'doliftban')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'blacklistid' => TYPE_INT
	));

	if (!$canunbanuser)
	{
		print_stop_message('no_permission_un_blacklist_users');
	}
	$email = $db->query_first("
		SELECT *
		FROM " . TABLE_PREFIX . "blacklist
		WHERE blacklist.blacklistid = " . $vbulletin->GPC['blacklistid'] . "
	");

	// check we got a record back and that the returned user is in a banned group
	if (!$email)
	{
		print_stop_message('invalid_email_specified');
	}

	$db->query_write("
		DELETE FROM " . TABLE_PREFIX . "blacklist
		WHERE blacklistid = ".$vbulletin->GPC['blacklistid']
	);	
	
	define('CP_REDIRECT', 'blacklist.php');
	print_stop_message('removed_blacklist_email_x_successfully', "<b>$email[email]</b>");
}

// #############################################################################
// ban a user

if ($_POST['do'] == 'doblacklist')
{
	$vbulletin->input->clean_array_gpc('p', array(
		'email_list'      => TYPE_STR
	));

	
	if (!$canbanuser)
	{
		print_stop_message('no_permission_ban_users');
	}
	$email_arr = explode("\n", $vbulletin->GPC['email_list']);
	

	//check if emails are validate
	$flag = 0;
	$error_str = '';
	foreach ($email_arr as $email_item ) {
		$email_item = trim($email_item);
		if ($email_item != '' && !filter_var($email_item,FILTER_VALIDATE_EMAIL)){
			$error_str .= 'Invalid Email Address Detected: '.$email_item.'<br/>';
			$flag = 1;
		}
	}
	if ($flag == 1) {
		print_stop_message('input_correct_emails_for_blacklist_x', $error_str);
	}
	foreach ($email_arr as $email_item ) 
	{
		// insert a record into the blacklist table
		$email_item = trim($email_item);
		/*insert query*/
		if ($email_item != '') {
			$email = $db->query_first("
				SELECT *
				FROM " . TABLE_PREFIX . "blacklist
				WHERE blacklist.email = '" . $email_item . "'
			");
			if (!$email) {
				$db->query_write("
					INSERT INTO " . TABLE_PREFIX . "blacklist
					(email,bandate)
					VALUES
					('$email_item','".TIMENOW."')
				");
			}
		}
	}

	define('CP_REDIRECT', 'blacklist.php');
	print_stop_message('add_emails_to_blacklist_success');
}

// #############################################################################
// user banning form

if ($_REQUEST['do'] == 'blacklist')
{

	if (!$canbanuser)
	{
		print_stop_message('no_permission_ban_users');
	}

	print_form_header('blacklist', 'doblacklist');
	print_table_header($vbphrase['blacklist']);
	print_textarea_row(
		"Email list <dfn>Please input email list separated by line breaks.</dfn>",
		'email_list',
		'',
		10, '45" style="width:100%',
		false,
		true,
		'ltr',
		false
	);
	print_submit_row('Add to Blacklist');
}

// #############################################################################
// display users from 'banned' usergroups

if ($_REQUEST['do'] == 'modify')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'pagenumber'   => TYPE_UINT,
	));

	$perpage = 20;
	if (!$vbulletin->GPC['pagenumber'])
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}
	$start = ($vbulletin->GPC['pagenumber'] - 1) * $perpage;

	function construct_banned_email_row($row)
	{
		global $vbulletin, $vbphrase;

		$cell = array($row['email']);
		
		if ($row['username']) {
			$cell[] = $row['username'];
		}
		else {
			$cell[] = $vbphrase['n_a'];
		}
		if ($row['bandate']) {
			$cell[] = vbdate($vbulletin->options['dateformat'], $row['bandate']);
		}
		else {
			$cell[] = $vbphrase['n_a'];
		}
		if (($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) OR can_moderate(0, 'canunbanusers'))
		{
			$cell[] = construct_link_code($vbphrase['remove_blacklist'], 'blacklist.php?' . $vbulletin->session->vars['sessionurl'] . "do=liftban&amp;blacklistid=$row[blacklistid]");
		}
		return $cell;
	}

	
	// define the column headings
	$headercell = array(
		$vbphrase['email'],
		$vbphrase['user_name'],
		$vbphrase['banned_on']
	);
	if (($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) OR can_moderate(0, 'canunbanusers'))
	{
		$headercell[] = $vbphrase['remove_blacklist'];
	}

	$havebanned = false;
	
	// now query users from the specified groups that are permanently banned
	$permusercount = $db->query_first("
		SELECT COUNT(*) AS count
		FROM " . TABLE_PREFIX . "blacklist
	");
	if ($permusercount['count'])
	{
		$havebanned = true;

		$pagecount = ceil($permusercount['count'] / $perpage);

		$bannedresult = $db->query_read("
			SELECT blacklist.blacklistid AS blacklistid, blacklist.email AS email, blacklist.bandate AS bandate, user.username AS username
			FROM " . TABLE_PREFIX . "blacklist AS blacklist
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON(blacklist.email = user.email)
			ORDER BY blacklist.blacklistid DESC
			LIMIT $start, $perpage
		");

		print_form_header('blacklist', 'blacklist');
		print_table_header($vbphrase['blacklist'], 8);
		if ($pagecount > 1)
		{
			$pagenav = "<strong>$vbphrase[go_to_page]</strong>";
			for ($thispage = 1; $thispage <= $pagecount; $thispage++)
			{
				if ($thispage == $vbulletin->GPC['pagenumber'])
				{
					$pagenav .= " <strong>[$thispage]</strong> ";
				}
				else
				{
					$pagenav .= " <a href=\"blacklist.php?$session[sessionurl]do=modify&amp;page=$thispage\" class=\"normal\">$thispage</a> ";
				}
			}

			print_description_row($pagenav, false, 8, '', 'right');
		}

		print_cells_row($headercell, 1);
		while ($row = $db->fetch_array($bannedresult))
		{
			print_cells_row(construct_banned_email_row($row));
		}
		print_submit_row('Add to Blacklist',0,8);
	}

	if (!$havebanned)
	{
		if ($canbanuser)
		{
			print_stop_message('no_users_blacklist_from_x_board_click_here', '<b>' . $vbulletin->options['bbtitle'] . '</b>', 'blacklist.php?' . $vbulletin->session->vars['sessionurl'] . 'do=blacklist');
		}
		else
		{
			print_stop_message('no_users_blacklist_from_x_board', '<b>' . $vbulletin->options['bbtitle'] . '</b>');
		}
	}

}

print_cp_footer();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 11:43, Mon Sep 8th 2008
|| # CVS: $RCSfile$ - $Revision: 26083 $
|| ####################################################################
\*======================================================================*/
?>
