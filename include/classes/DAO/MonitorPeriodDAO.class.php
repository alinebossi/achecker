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
 * DAO for "monitor_period" table
 * @access	public
 * @author	Aline Bossi Pereira da Silva
 * @package	DAO
 */
 
if (!defined('AC_INCLUDE_PATH')) exit;

require_once(AC_INCLUDE_PATH. 'classes/DAO/DAO.class.php');

class MonitorPeriodDAO extends DAO {

	/**
	 * Create a new monitor period
	 * @access  public
	 * @param   monitor period id
	 *          description 
	 *          value
	 * @return  monitor period id, if successful
	 *          false and add error into global var $msg, if unsuccessful
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function Create($description, $value)
	{
		global $addslashes, $msg;

		$missing_fields = array();

		$description = $addslashes(trim($description));

		if ($description == '')
		{
			$missing_fields[] = _AC('description');
		}
		
		if ($value == '')
		{
			$missing_fields[] = _AC('value');
		}

		if ($missing_fields)
		{
			$missing_fields = implode(', ', $missing_fields);
			$msg->addError(array('EMPTY_FIELDS', $missing_fields));
		}

		if (!$msg->containsErrors())
		{
			/* insert into the db */
			$sql = "INSERT INTO ".TABLE_PREFIX."monitor_period
			              (description,
						   value,
			               create_date
			               )
			       VALUES ('".$description."',
			               '".$value."',
						   now()
			              )";

			if (!$this->execute($sql))
			{
				$msg->addError('DB_NOT_UPDATED');
				return false;
			}
			else
			{
				return mysql_insert_id();
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Update an existing monitor period
	 * @access  public
	 * @param   monitor period id
	 *          description 
	 *          value
	 * @return  monitor id, if successful
	 *          false and add error into global var $msg, if unsuccessful
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function Update($period_id, $description, $value)
	{
		global $addslashes, $msg;

		$missing_fields = array();

		$period_id = intval($period_id);
		$description = $addslashes(trim($description));
		$value = intval($value);
		
		/* login name check */
		if ($description == '')
		{
			$missing_fields[] = _AC('description');
		}

		if ($missing_fields)
		{
			$missing_fields = implode(', ', $missing_fields);
			$msg->addError(array('EMPTY_FIELDS', $missing_fields));
		}

		if (!$msg->containsErrors())
		{
			/* insert into the db */
			$sql = "UPDATE ".TABLE_PREFIX."monitor_period
			           SET description = '".$description."',
						   value = '".$value."',
			               last_update = now()
			         WHERE period_id = ".$period_id;

			return $this->execute($sql);
		}
	}

	/**
	 * delete monitor period by given id
	 * @access  public
	 * @param   monitor period id
	 * @return  true / false
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function Delete($period_id)
	{
		$period_id = intval($period_id);
		
		// delete user_groups
		$sql = 'DELETE FROM '.TABLE_PREFIX.'monitor_period WHERE period_id = '.$period_id;
		
		return $this->execute($sql);
	}
	
	/**
	 * Return monitor period information by given id
	 * @access  public
	 * @param   monitor period id
	 * @return  monitor period row
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getPeriodByID($period_id)
	{
		$period_id = intval($period_id);
		
		$sql = 'SELECT * FROM '.TABLE_PREFIX.'monitor_period WHERE period_id='.$period_id;
		if ($rows = $this->execute($sql))
		{
			return $rows[0];
		}
	}

	/**
	 * Return monitor period description by given id
	 * @access  public
	 * @param   monitor period id
	 * @return  description
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getPeriodDescription($period_id)
	{
		$period_id = intval($period_id);
		
		$row = $this->getPeriodByID($period_id);
		
		if (!$row) return false;
		
		return $row['description'];
	}
	
	/**
	 * Return all monitor period' information
	 * @access  public
	 * @param   none
	 * @return  monitor period row
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getAll()
	{
		$sql = 'SELECT * FROM '.TABLE_PREFIX.'monitor_period ORDER BY value';
		return $this->execute($sql);
	}

}
?>