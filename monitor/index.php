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
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorResultDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/UserLinksDAO.class.php');

// initialize constants
$results_per_page = 15;
$dao = new DAO();
$MonitorDAO = new MonitorDAO();

// handle submit
if ( (isset($_GET['edit']) || isset($_GET['check']) || isset($_GET['review']) || isset($_GET['delete'])) && !isset($_GET['id']) ) {
	$msg->addError('NO_ITEM_SELECTED');
// handle edit
} else if (isset($_GET['edit'], $_GET['id'])) {
	header('Location: monitor_create_edit.php?id='.$_GET['id']);
	exit;
// handle review
} else if ( isset($_GET['review'], $_GET['id'])) {
	header('Location: monitor_review.php?id='.$_GET['id']);
	exit;
// handle check
} else if ( isset($_GET['check'], $_GET['id'])) {
	$has_enough_memory = true;
		
	$monitor = $MonitorDAO->getMonitorByID($_GET['id']);
	
	$uri = Utility::getValidURI($addslashes($monitor["URL"]));
	
	// Check if the given URI is connectable
	if ($uri === false)
	{
		$msg->addError(array('CANNOT_CONNECT', $monitor["URL"]));
	}
	
	// don't accept localhost URI
	if (stripos($uri, '://localhost') > 0)
	{
		$msg->addError('NOT_LOCALHOST');
	}
	
	$validate_content = @file_get_contents($uri);
	
	if (isset($validate_content) && !Utility::hasEnoughMemory(strlen($validate_content)))
	{
		$msg->addError('NO_ENOUGH_MEMORY');
		$has_enough_memory = false;
	}
	
	$guid_id = array($monitor["guideline_id"]);
		
	include(AC_INCLUDE_PATH. "classes/AccessibilityValidator.class.php");
	
	if (isset($validate_content) && $has_enough_memory)
	{
		$aValidator = new AccessibilityValidator($validate_content, $guid_id, $uri);
		$aValidator->validate();
		
		$num_of_total_a_errors = $aValidator->getNumOfValidateError();
		$errors = $aValidator->getValidationErrorRpt();
				
		$userLinksDAO = new UserLinksDAO();
		$user_link_id = $userLinksDAO->getUserLinkID($_SESSION['user_id'], $uri, $guid_id);
		
		$MonitorResultDAO = new MonitorResultDAO();
		$MonitorResultDAO->create(AC_MANUAL, $_GET['id'], $guid_id, $user_link_id, $num_of_total_a_errors, $errors);
		
		if (!$msg->containsErrors())
		{
			$MonitorResultDAO->generateRpt();
		}
						
		if (!$msg->containsErrors())
		{
			$msg->addFeedback('ACTION_COMPLETED_SUCCESSFULLY');
		}
	}	
	
	if (defined('AC_EMAIL_CHECK') && AC_EMAIL_CHECK) 
	{
		/* send the email confirmation message: */		
		require(AC_INCLUDE_PATH . 'classes/phpmailer/acheckermailer.class.php');
		$mail = new ACheckerMailer();
		
		$mail->CharSet = 'UTF-8';
		$mail->From     = $_config['contact_email'];
		$mail->FromName = $_config['contact_name'];
		$mail->AddAddress($monitor["email"]);
		$mail->Subject = SITE_NAME . ' - ' . _AC('email_check_subject');
		if (!$msg->containsErrors())
		{
			//$mail->Body    = _AC('email_check_message_success', $monitor["URL"], $MonitorResultDAO->getMonitorID(),$MonitorResultDAO->getResultID(),$msg->GetAll())."<br><br>";
			$mail->Body    = _AC('email_check_message_success', $monitor["URL"], $MonitorResultDAO->getMonitorID(),$MonitorResultDAO->getResultID())."<br><br>";
		} else {
			$mail->Body    = _AC('email_check_message_unsuccess', $monitor["URL"], $msg->GetAll())."<br><br>";
		}
		$mail->IsHTML(true);
		$mail->Send();
	}
	
	header('Location: index.php');
	exit;
// handle delete	
} else if ( isset($_GET['delete'], $_GET['id'])) {
	header('Location: monitor_delete.php?id='.$_GET['id'][0]);
	exit;
}

// page initialize
if ($_GET['reset_filter']) {
	unset($_GET);
}

$page_string = '';
$orders = array('asc' => 'desc', 'desc' => 'asc');
$cols   = array('title' => 1, 'description' => 1, 'URL' => 1, 'monitor_period' => 1, 'email' => 1, 'guidelines' => 1, 'status' => 1);

if (isset($_GET['asc'])) {
	$order = 'asc';
	$col   = isset($cols[$_GET['asc']]) ? $_GET['asc'] : 'title';
} else if (isset($_GET['desc'])) {
	$order = 'desc';
	$col   = isset($cols[$_GET['desc']]) ? $_GET['desc'] : 'title';
} else {
	// no order set
	$order = 'asc';
	$col   = 'title';
}
if (isset($_GET['status']) && ($_GET['status'] != '')) {
	$_GET['status'] = intval($_GET['status']);
	$status = '=' . intval($_GET['status']);
	$page_string .= htmlspecialchars(SEP).'status'.$status;
} else {
	$status = '<>-1';
	$_GET['status'] = '';
}

if (isset($_GET['include']) && $_GET['include'] == 'one') {
	$checked_include_one = ' checked="checked"';
	$page_string .= htmlspecialchars(SEP).'include=one';
} else {
	$_GET['include'] = 'all';
	$checked_include_all = ' checked="checked"';
	$page_string .= htmlspecialchars(SEP).'include=all';
}

if ($_GET['search']) {
	$page_string .= htmlspecialchars(SEP).'search='.urlencode($stripslashes($_GET['search']));
	$search = $addslashes($_GET['search']);
	$search = explode(' ', $search);

	if ($_GET['include'] == 'all') {
		$predicate = 'AND ';
	} else {
		$predicate = 'OR ';
	}

	$sql = '';
	foreach ($search as $term) {
		$term = trim($term);
		$term = str_replace(array('%','_'), array('\%', '\_'), $term);
		if ($term) {
			$term = '%'.$term.'%';
			$sql .= "((M.title LIKE '$term') OR (M.description LIKE '$term') OR (M.email LIKE '$term') OR (M.URL LIKE '$term')) $predicate";
		}
	}
	$sql = '('.substr($sql, 0, -strlen($predicate)).')';
	$search = $sql;
} else {
	$search = '1';
}

if ($_GET['guideline_id'] && $_GET['guideline_id'] <> -1) {
	$guideline_sql = "M.guideline_id = ".$_GET['guideline_id'];
	$page_string .= htmlspecialchars(SEP).'guideline_id='.urlencode($_GET['guideline_id']);
}
else
{
	$guideline_sql = '1';
}

if ($_current_user->isAdmin())
{
	$user = '1';
}
else
{
	$user = "M.user_id = ".$_SESSION['user_id'];
}


$sql	= "SELECT COUNT(monitor_id) AS cnt FROM ".TABLE_PREFIX."monitor M WHERE $user AND status $status AND $search AND $guideline_sql";

$rows = $dao->execute($sql);
$num_results = $rows[0]['cnt'];

$num_pages = max(ceil($num_results / $results_per_page), 1);
$page = intval($_GET['p']);
if (!$page) {
	$page = 1;
}	
$count  = (($page-1) * $results_per_page) + 1;
$offset = ($page-1)*$results_per_page;

$sql = "SELECT M.monitor_id, M.title, M.description, M.URL, MP.description monitor_period, M.email, G.abbr guidelines, M.status, M.last_update
          FROM ".TABLE_PREFIX."monitor M, ".TABLE_PREFIX."monitor_period MP, ".TABLE_PREFIX."guidelines G
          WHERE M.period_id = MP.period_id
		  AND M.guideline_id = G.guideline_id
          AND $user AND M.status $status AND $search AND $guideline_sql ORDER BY $col $order LIMIT $offset, $results_per_page";

$monitor_rows = $dao->execute($sql);

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

$savant->assign('monitor_rows', $monitor_rows);
$savant->assign('all_guidelines', $guidelines);
$savant->assign('results_per_page', $results_per_page);
$savant->assign('num_results', $num_results);
$savant->assign('checked_include_all', $checked_include_all);
$savant->assign('page',$page);
$savant->assign('page_string', $page_string);
$savant->assign('orders', $orders);
$savant->assign('order', $order);
$savant->assign('col', $col);

$savant->display('monitor/index.tmpl.php');

?>
