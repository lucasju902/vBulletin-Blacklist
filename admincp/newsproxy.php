<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.11 Patch Level 1 - Licence Number 3578c1c3
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2008 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// ##################### DEFINE IMPORTANT CONSTANTS #######################
define('CVS_REVISION', '$RCSfile$ - $Revision: 15737 $');

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array();

$specialtemplates = array();

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/class_rss_poster.php');

header('Content-Type: text/xml; charset=utf-8');

if ($result = fetch_file_via_socket('http://version.vbulletin.com/news.xml?v=' . SIMPLE_VERSION . '&id=3578c1c3', array('type' => '')))
{	
	echo $result['body'];
}
else
{
	echo 'Error';
}


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 11:43, Mon Sep 8th 2008
|| # CVS: $RCSfile$ - $Revision: 15737 $
|| ####################################################################
\*======================================================================*/
?>