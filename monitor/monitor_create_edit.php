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
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorPeriodDAO.class.php');

unset($id);  // clean up the temporary id values set by vitals.inc.php

if (isset($_GET["id"])) $id = intval($_GET["id"]);

$MonitorDAO = new MonitorDAO();

// handle submits
if (isset($_POST['cancel'])) 
{
	$msg->addFeedback('CANCELLED');
	header('Location: index.php');
	exit;
} 
else if (isset($_POST['save']))
{
	if (isset($id))  // edit existing monitor
	{
		$MonitorDAO->update($id,
							$_POST['title'], 
							$_POST['description'],
							$_POST['url'],
							$_POST['period_id'],
							$_POST['email'],
							$_POST['status'],
							$_POST['guideline_id'],
							$_POST['show_source']);
	}
	else  // create a new monitor
	{
		$id = $MonitorDAO->Create($_POST['title'], 
								  $_POST['description'],
								  $_POST['url'],
								  $_POST['period_id'],
								  $_POST['email'],
								  $_SESSION['user_id'],
								  $_POST['status'],
								  $_POST['guideline_id'],
								  $_POST['show_source']);
	}

	if (!$msg->containsErrors())
	{
		$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		header('Location: index.php');
		exit;
	}
}
else if (isset($_POST['delete']))
{
	$MonitorDAO->Delete($id);
}

// interface display
$monitorperiodDAO = new MonitorPeriodDAO();
$monitorperiod = $monitorperiodDAO->getAll();

$guidelinesDAO = new GuidelinesDAO();
$open_guidelines = $guidelinesDAO->getOpenGuidelines();

if (isset($_current_user))
{
	$user_guidelines = $guidelinesDAO->getClosedEnabledGuidelinesByUserID($_SESSION['user_id']);
	if (is_array($user_guidelines)) 
		$guidelines = array_merge($open_guidelines, $user_guidelines);
	else
		$guidelines = $open_guidelines;
}
else
{
	$guidelines = $open_guidelines;
}
	
$savant->assign('all_guidelines', $guidelines);
$savant->assign('all_monitorperiod', $monitorperiod);

if (isset($id))
{
	$savant->assign('monitor_row', $MonitorDAO->getMonitorByID($id));
}

$savant->display('monitor/monitor_create_edit.tmpl.php');
?>
