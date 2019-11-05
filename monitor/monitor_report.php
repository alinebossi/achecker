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
include_once(AC_INCLUDE_PATH.'classes/HTMLRpt.class.php');
include_once(AC_INCLUDE_PATH.'classes/HTMLByGuidelineRpt.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/MonitorResultDAO.class.php');

// handle submit
if ( !isset($_GET['id']) || !isset($_GET['result']) ) 
	{
		$msg->addError('NO_ITEM_SELECTED');
	} 
else if ( isset($_GET['id'],$_GET['result'])) 
	{
		$MonitorResultDAO = new MonitorResultDAO();
		$MonitorResult = $MonitorResultDAO->getResultByID($_GET['id'], $_GET['result']);
		
		if (!$msg->containsErrors())
		{
			$_gids = array($MonitorResult["guideline_id"]);

			// find out selected guidelines
			$guidelinesDAO = new GuidelinesDAO();
			$guideline_rows = $guidelinesDAO->getGuidelineByIDs($_gids);
				
			$guidelines_text = "";
			if (is_array($guideline_rows))
			{
				foreach ($guideline_rows as $id => $row)
				{
					$guidelines_text .= '<a title="'.$row["title"]._AC('link_open_in_new').'" target="_new" href="'.AC_BASE_HREF.'guideline/view_guideline.php?id='.$row["guideline_id"].'">'.$row["title"]. '</a>, ';
				}
			}
			$guidelines_text = substr($guidelines_text, 0, -2); // remove ending space and ,

			$num_of_total_a_errors = $MonitorResultDAO->getNumOfValidateError();
			$errors = $MonitorResultDAO->getValidationErrorRpt();
			$user_link_id = $MonitorResultDAO->getUserLinkID();
			
			$from_referer = 'false';			
			$allow_set_decision = 'false';
			
			$a_rpt = new HTMLByGuidelineRpt($errors, $_gids[0], $user_link_id);

			$a_rpt->setAllowSetDecisions($allow_set_decision);
			$a_rpt->setFromReferer($from_referer);
			
			$a_rpt->generateRpt();
			
			$num_of_errors = $a_rpt->getNumOfErrors();
			$num_of_likely_problems = $a_rpt->getNumOfLikelyProblems();
			$num_of_likely_problems_no_decision = $a_rpt->getNumOfLikelyWithFailDecisions();
			$num_of_potential_problems = $a_rpt->getNumOfPotentialProblems();
			$num_of_potential_problems_no_decision = $a_rpt->getNumOfPotentialWithFailDecisions();
			
			// no any problems or all problems have pass decisions, display seals when no errors
			$seals = null;
			if ($num_of_errors == 0 && 
				($num_of_likely_problems == 0 && $num_of_potential_problems == 0 ||
				 $num_of_likely_problems_no_decision == 0 && $num_of_potential_problems_no_decision == 0))
			{
				$utility = new Utility();
				$seals = $utility->getSeals($guideline_rows);
			}
			
			$savant->assign('fail', false);
			$savant->assign('a_rpt', $a_rpt);
			$savant->assign('num_of_errors', $num_of_errors);
			$savant->assign('num_of_likely_problems', $num_of_likely_problems);
			$savant->assign('num_of_likely_problems_no_decision', $num_of_likely_problems_no_decision);
			$savant->assign('num_of_potential_problems', $num_of_potential_problems);
			$savant->assign('num_of_potential_problems_no_decision', $num_of_potential_problems_no_decision);

			$savant->assign('aValidator', $MonitorResultDAO);
			$savant->assign('guidelines_text', $guidelines_text);
			$savant->assign('num_of_total_a_errors', $num_of_total_a_errors);
			
			// vars for displaying seals
			if (is_array($seals)) {
				$savant->assign('savant', $savant);
				$savant->assign('seals', $seals);
			}
			if ($user_link_id <> '') $savant->assign('user_link_id', $user_link_id);
			
			// vars for displaying report from referer URI
			if ($_REQUEST['uri'] == 'referer')
			{
				$savant->assign('referer_report', 1);
				if (intval($user_link_id) > 0) $savant->assign('referer_user_link_id', $user_link_id);
			}
		} else {
			$savant->assign('fail', true);
		}
	}

$savant->display('monitor/monitor_report.tmpl.php');
?>