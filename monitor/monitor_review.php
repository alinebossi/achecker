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
include_once(AC_INCLUDE_PATH.'classes/Utility.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorDAO.class.php');

// initialize constants
$results_per_page = 15;
$dao = new DAO();
$MonitorDAO = new MonitorDAO();

if ( !isset($_GET['asc']) && !isset($_GET['desc']) ) {
	unset($_SESSION['monitor_review_id']);
}
if ( isset($_GET['id']) && !isset($_GET['result_id']) ) {
	$_SESSION['monitor_review_id'] = $_GET['id'];
} else if ( isset($_GET['view_report'], $_GET['id'], $_GET['result_id']) ) {
	header('Location: monitor_report.php?id='.$_GET['id'].'&result='.$_GET['result_id']);
	exit;
}

$page_string = '';
$orders = array('asc' => 'desc', 'desc' => 'asc');
$cols   = array('create_date' => 1, 'guidelines' => 1, 'total_errors' => 1, 'errors' => 1, 'likely_problems' => 1, 'potential_problems' => 1, 'made_decisions' => 1);

if (isset($_GET['asc'])) {
	$order = 'asc';
	$col   = isset($cols[$_GET['asc']]) ? $_GET['asc'] : 'create_date';
} else if (isset($_GET['desc'])) {
	$order = 'desc';
	$col   = isset($cols[$_GET['desc']]) ? $_GET['desc'] : 'create_date';
} else {
	// no order set
	$order = 'desc';
	$col   = 'create_date';
}

$monitor = "MR.monitor_id = ".$_SESSION['monitor_review_id'];

$sql	= "SELECT COUNT(result_id) AS cnt FROM ".TABLE_PREFIX."monitor_result MR WHERE $monitor";

$rows = $dao->execute($sql);
$num_results = $rows[0]['cnt'];

$num_pages = max(ceil($num_results / $results_per_page), 1);
$page = intval($_GET['p']);
if (!$page) {
	$page = 1;
}	
$count  = (($page-1) * $results_per_page) + 1;
$offset = ($page-1)*$results_per_page;

$sql = "SELECT MR.result_id, MR.create_date, G.abbr guidelines, MR.total_errors, MR.errors, MR.likely_problems, MR.potential_problems, MR.made_decisions
		FROM ".TABLE_PREFIX."monitor_result MR, ".TABLE_PREFIX."guidelines G
		WHERE $monitor
		  AND MR.guideline_id = G.guideline_id
		ORDER BY $col $order LIMIT $offset, $results_per_page";

$monitor_result_rows = $dao->execute($sql);

$monitor_row = $MonitorDAO->getMonitorByID($_SESSION['monitor_review_id']);
$title = $monitor_row['title'];
$URL = $monitor_row['URL'];

$savant->assign('monitor_result_rows', $monitor_result_rows);
$savant->assign('monitor', $_SESSION['monitor_review_id']);
$savant->assign('title', $title);
$savant->assign('URL', $URL);
$savant->assign('results_per_page', $results_per_page);
$savant->assign('num_results', $num_results);
$savant->assign('page',$page);
$savant->assign('page_string', $page_string);
$savant->assign('orders', $orders);
$savant->assign('order', $order);
$savant->assign('col', $col);

$savant->display('monitor/monitor_review.tmpl.php');

?>