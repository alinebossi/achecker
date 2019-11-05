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

unset($id);  // clean up the temporary id values set by vitals.inc.php

if (isset($_GET["id"])) $id = intval($_GET["id"]);

$MonitorPeriodDAO = new MonitorPeriodDAO();

// handle submits
if (isset($_POST['cancel'])) 
{
	$msg->addFeedback('CANCELLED');
	header('Location: monitor_period.php');
	exit;
} 
else if (isset($_POST['save']))
{
	if (isset($id))  // edit existing monitor
	{
		$MonitorPeriodDAO->update($id,
							$_POST['description'],
							$_POST['value']);
	}
	else  // create a new monitor
	{
		$id = $MonitorPeriodDAO->Create($_POST['description'],
								  $_POST['value']);
	}

	if (!$msg->containsErrors())
	{
		$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		header('Location: monitor_period.php');
		exit;
	}
}
else if (isset($_POST['delete']))
{
	$MonitorPeriodDAO->Delete($id);
}

if (isset($id))
{
	$savant->assign('period_row', $MonitorPeriodDAO->getPeriodByID($id));
}

$savant->display('monitor/monitor_period_create_edit.tmpl.php');
?>
