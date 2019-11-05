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

/**
 * DAO for "monitor" table
 * @access	public
 * @author	Aline Bossi Pereira da Silva
 * @package	DAO
 */
 
if (!defined('AC_INCLUDE_PATH')) exit;

include_once(AC_INCLUDE_PATH.'classes/DAO/UserDecisionsDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/ChecksDAO.class.php');
require_once(AC_INCLUDE_PATH.'classes/DAO/DAO.class.php');

class MonitorResultDAO extends DAO {
	
	var $errors = array();               	// all check results, including success ones and failed ones
	
	var $monitor_id;						// monitor id
	var $result_id;						 	// result id returned from insert on build method
	var $user_link_id;						// user link id for decisions
	
	var $num_of_errors;                  	// Number of known errors. (db: checks.confidence = "Known")
	var $num_of_likely_problems;         	// Number of likely errors. (db: checks.confidence = "Likely")
	var $num_of_potential_problems;      	// Number of potential errors. (db: checks.confidence = "Potential")
	
	var $num_of_likely_problems_fail;       // Number of likely errors that decisions have not been made
	var $num_of_potential_problems_fail;    // Number of potential errors that decisions have not been made
	
	var $num_of_no_decisions;           	// Number of likely/potential errors that decisions have not been made
	var $num_of_made_decisions;            	// Number of likely/potential errors that decisions have been made
	
	/**
	 * Create a new monitor result
	 * @access  public
	 * @param   monitor_id
	 *  		total_errors        
	 *			errors_rpt
	 * @author  Aline Bossi Pereira da Silva
	 */
	function create($type, $monitor_id, $guid_id, $user_link_id, $total_errors, $errors)
	{	
		global $addslashes, $msg;
		
		$this->num_of_errors = 0;
		$this->num_of_likely_problems = 0;
		$this->num_of_potential_problems = 0;
		
		$this->num_of_likely_problems_fail = 0;
		$this->num_of_potential_problems_fail = 0;
		
		$this->num_of_no_decisions = 0;
		$this->num_of_made_decisions = 0;
		
		$this->errors = $errors;
		$missing_fields = array();
		
		$this->monitor_id = intval($monitor_id);
		$guideline_id = intval($guid_id[0]);
		$this->user_link_id = intval($user_link_id);
		$type = intval($type);
		$total_errors = intval($total_errors);
		
		if ($this->monitor_id <= 0)
		{
			$missing_fields[] = _AC('monitor_id');
		}
		
		if ($guideline_id <= 0)
		{
			$missing_fields[] = _AC('guidelines');
		}
		
		if ($this->user_link_id <= 0)
		{
			$missing_fields[] = _AC('user_link_id');
		}
		
		if ($missing_fields)
		{
			$missing_fields = implode(', ', $missing_fields);
			$msg->addError(array('EMPTY_FIELDS', $missing_fields));
		}
		
		if (!$msg->containsErrors())
		{
			/* insert into the db */
			$sql = "INSERT INTO ".TABLE_PREFIX."monitor_result
			              (monitor_id,
						   guideline_id,
						   user_link_id,
						   type,
			               total_errors,
			               create_date
						   )
			       VALUES ('".$this->monitor_id."',
						   '".$guideline_id."',
						   '".$this->user_link_id."',
						   '".$type."',
			               '".$total_errors."',
			               now()
			              )";
			
			if (!$this->execute($sql))
			{
				$msg->addError('DB_NOT_UPDATEDD');
			}
			else
			{
				$this->result_id = mysql_insert_id();
			}
		}
	}
	
	/**
	* process to update count errors in database
	* @access  private
	* @author  Aline Bossi Pereira da Silva
	*/
	private function update_count()
	{
		global $msg;
		/* update into the db */
		$sql = "UPDATE ".TABLE_PREFIX."monitor_result
				   SET errors = '".$this->num_of_errors."',
					   likely_problems = '".$this->num_of_likely_problems."',
					   likely_with_fail_decisions = '".$this->num_of_likely_problems_fail."',
					   potential_problems = '".$this->num_of_potential_problems."',
					   potential_with_fail_decisions = '".$this->num_of_potential_problems_fail."',
					   no_decisions = '".$this->num_of_no_decisions."',
					   made_decisions = '".$this->num_of_made_decisions."'
				WHERE monitor_id = '".$this->monitor_id."'
				  AND result_id = '".$this->result_id."'";
		
		$result = $this->execute($sql);
		if (!$result)
		{
			$msg->addError('DB_NOT_UPDATEDD');
		}
	}
	
	/**
	* process to save errors in database
	* @access  private
	* @author  Aline Bossi Pereira da Silva
	*/
	private function save_error($error_id, $line_number, $col_number, $html_code, $check_id, $result, $image, $image_alt, $css_code)
	{
		global $addslashes, $msg;
		
		$error_id = intval($error_id);
		$line_number = intval($line_number);
		$col_number = intval($col_number);
		$html_code = $addslashes(trim($html_code));
		$check_id = intval($check_id);
		$result = $addslashes(trim($result));
		$image = $addslashes(trim($image));
		$image_alt = $addslashes(trim($image_alt));
		$css_code = $addslashes(trim($css_code));
		
		/* insert into the db */
		$sql = "INSERT INTO ".TABLE_PREFIX."monitor_result_errors
					  (monitor_id,
					   result_id,
					   error_id,
					   line_number,
					   col_number,
					   html_code,
					   check_id,
					   result,
					   image,
					   image_alt,
					   css_code
					   )
			   VALUES ('".$this->monitor_id."',
					   '".$this->result_id."',
					   '".$error_id."',
					   '".$line_number."',
					   '".$col_number."',
					   '".$html_code."',
					   '".$check_id."',
					   '".$result."',
					   '".$image."',
					   '".$image_alt."',
					   '".$css_code."'
					  )";
		
		if (!$this->execute($sql))
		{
			$msg->addError('DB_NOT_UPDATEDD');
		}
	}
	
	/**
	* process to save errors in database
	* @access  private
	* @author  Aline Bossi Pereira da Silva
	*/
	private function save_error_with_decision($error_id, $line_number, $col_number, $html_code, $check_id, $result, $image, $image_alt, $css_code, $type)
	{
		// generate decision section
		$userDecisionsDAO = new UserDecisionsDAO();
		$row = $userDecisionsDAO->getByUserLinkIDAndLineNumAndColNumAndCheckID($this->user_link_id, $line_number, $col_number, $check_id);
		
		if (!$row || $row['decision'] == AC_DECISION_FAIL) 
		{ // no decision or decision of fail
			if ($type == IS_WARNING) $this->num_of_likely_problems_fail++;
			if ($type == IS_INFO) $this->num_of_potential_problems_fail++;
		}
		
		if (!$row) // no decision
		{
			$this->num_of_no_decisions++;
		}
		else
		{
			$this->num_of_made_decisions++;
		}
		$this->save_error($error_id, $line_number, $col_number, $html_code, $check_id, $result, $image, $image_alt, $css_code);
	}
	
	/**
	* main process to generate report in database
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	function generateRpt()
	{
		global $msg;
		$checksDAO = new ChecksDAO();
		
		$error_id = 0;
		foreach ($this->errors as $error)
		{
			$row = $checksDAO->getCheckByID($error["check_id"]);
			$error_id++;
			if ($row["confidence"] == KNOWN )
			{ // no decision to make on known problems
				$this->num_of_errors++;
				$this->save_error($error_id, $error["line_number"], $error["col_number"], $error["html_code"], $error["check_id"], $error["result"], $error["image"], $error["image_alt"], $error["css_code"]);
			}
			else if ($row["confidence"] == LIKELY )
			{
				$this->num_of_likely_problems++;
				$this->save_error_with_decision($error_id, $error["line_number"], $error["col_number"], $error["html_code"], $error["check_id"], $error["result"], $error["image"], $error["image_alt"], $error["css_code"], IS_WARNING);
			}
			else if ($row["confidence"] == POTENTIAL )
			{
				$this->num_of_potential_problems++;
				$this->save_error_with_decision($error_id, $error["line_number"], $error["col_number"], $error["html_code"], $error["check_id"], $error["result"], $error["image"], $error["image_alt"], $error["css_code"], IS_INFO);
			}
		}
		
		$this->update_count();		
	}
	
	/**
	* return number of errors
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	public function getNumOfValidateError()
	{
		return $this->num_of_errors;
	}
	
	/**
	* return monitor id
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	public function getMonitorID()
	{
		return $this->monitor_id;
	}

	/**
	* return result id
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	public function getResultID()
	{
		return $this->result_id;
	}
	
	/**
	* return user link id
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	public function getUserLinkID()
	{
		return $this->user_link_id;
	}
	
	/**
	* return array of all checks that have been done, including successful and failed ones
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	public function getResultByID($monitor_id, $result_id)
	{
		global $msg;
		
		$this->monitor_id = intval($monitor_id);
		$this->result_id = intval($result_id);
		
		$sql = 'SELECT * 
		        FROM '.TABLE_PREFIX.'monitor_result 
				WHERE monitor_id='.$this->monitor_id.' AND result_id='.$this->result_id;
		
		$rows = $this->execute($sql);
		
		if (is_array($rows))
		{	
			$this->user_link_id = $rows[0]['user_link_id'];
			$this->num_of_errors = $rows[0]['num_of_errors'];
			$this->num_of_likely_problems = $rows[0]['num_of_likely_problems'];
			$this->num_of_likely_problems_fail = $rows[0]['num_of_likely_problems_fail'];
			$this->num_of_potential_problems = $rows[0]['num_of_potential_problems'];
			$this->num_of_potential_problems_fail = $rows[0]['num_of_potential_problems_fail'];
			$this->num_of_no_decisions = $rows[0]['num_of_no_decisions'];
			$this->num_of_made_decisions = $rows[0]['num_of_made_decisions'];
			
			return $rows[0];
		}
		else
		{
			$msg->addError('CHECK_NOT_MATCH');
			return false;
		}
	}
	
	/**
	* return array of all checks that have been done, including successful and failed ones
	* @access  public
	* @author  Aline Bossi Pereira da Silva
	*/
	public function getValidationErrorRpt()
	{
		global $msg;
		
		$sql = 'SELECT *
		        FROM '.TABLE_PREFIX.'monitor_result_errors 
		        WHERE monitor_id='.$this->monitor_id.' AND result_id='.$this->result_id;
				
		$rows = $this->execute($sql);
		if (is_array($rows))
		{	
			foreach ($rows as $row)
			{
				array_push($this->errors, array("line_number"=>$row["line_number"], "col_number"=>$row["col_number"], "html_code"=>$row["html_code"], "check_id"=>$row["check_id"], "result"=>$row["result"], "image"=>$row["image"], "image_alt"=>$row["image_alt"], "css_code"=>$row["css_code"]));
			}
			return $this->errors;
		}
		else
		{
			$msg->addError('CHECK_NOT_MATCH');
			return false;
		}
	}
}