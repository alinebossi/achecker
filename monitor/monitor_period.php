<?php
/************************************************************************/
/* AChecker                                                             */
/************************************************************************/
/* Copyright (c) 2008 - 2011                                            */
/* Inclusive Design Institute                                           */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

define('AC_INCLUDE_PATH', '../include/');
include(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorPeriodDAO.class.php');

global $_current_user;

$monitorperiodDAO = new MonitorPeriodDAO();

if ((isset($_GET['delete']) || isset($_GET['edit'])) && !isset($_GET['id']))
{
	$msg->addError('NO_ITEM_SELECTED');
} 
else if ($_GET['delete'])
{
	header('Location: monitor_period_delete.php?id='.$_GET['id']);
	exit;
}
else if ($_GET['edit'])
{
	header('Location: monitor_period_create_edit.php?id='.$_GET['id']);
	exit;
}

include(AC_INCLUDE_PATH.'header.inc.php');

$savant->assign('rows', $monitorperiodDAO->getAll());
$savant->assign('formName', 'monitor_period');
$savant->display('monitor/monitor_period.tmpl.php');

// display footer
include(AC_INCLUDE_PATH.'footer.inc.php');

?>
