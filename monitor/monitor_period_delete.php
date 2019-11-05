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
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorPeriodDAO.class.php');

$MonitorDAO = new MonitorDAO();
$MonitorPeriodDAO = new MonitorPeriodDAO();

$id = $_REQUEST['id'];

if (isset($_POST['submit_no'])) 
{
	$msg->addFeedback('CANCELLED');
	header('Location: monitor_period.php');
	exit;
} 
else if (isset($_POST['submit_yes']))
{
	$MonitorPeriodDAO->Delete($id);

	$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
	header('Location: monitor_period.php');
	exit;
}

$count_period = $MonitorDAO->getCountByPeriod($id);

if ($count_period > 0)
{
	$msg->addError('PERIOD_IN_USE');
	header('Location: monitor_period.php');
	exit;
}

require(AC_INCLUDE_PATH.'header.inc.php');

unset($hidden_vars);

$name = $MonitorPeriodDAO->getPeriodDescription($id);

$name_html = '<ul>'.$name.'</ul>';
$hidden_vars['id'] = $_REQUEST['id'];

$msg->addConfirm(array('DELETE_MONITOR_PERIOD', $name_html), $hidden_vars);
$msg->printConfirm();

require(AC_INCLUDE_PATH.'footer.inc.php');
?>
