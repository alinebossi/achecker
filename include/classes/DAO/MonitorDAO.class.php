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

require_once(AC_INCLUDE_PATH. 'classes/DAO/DAO.class.php');

class MonitorDAO extends DAO {

	/**
	 * Create a new monitor
	 * @access  public
	 * @param   title
	 *          description
	 *          url
	 *          period_id
	 *          email
	 *          user_id
	 *          status
	 *          guideline_id
	 *          show_source
	 * @return  monitor id, if successful
	 *          false and add error into global var $msg, if unsuccessful
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function Create($title, $description, $url, $period_id, $email, $user_id, $status, $guideline_id, $show_source)
	{
		global $addslashes, $msg;

		$missing_fields = array();
		
		$title = $addslashes(trim($title));
		$description = $addslashes(trim($description));
		$url = $addslashes(trim($url));
		$period_id = intval($period_id);
		$email = $addslashes(trim($email));
		$user_id = intval($user_id);
		$status = intval($status);
		$guideline_id = intval($guideline_id);
		$show_source = intval($show_source);
		
		if ($title == '')
		{
			$missing_fields[] = _AC('title');
		}
		
		if ($url == '')
		{
			$missing_fields[] = _AC('url');
		}
		
		if ($period_id <= 0)
		{
			$missing_fields[] = _AC('period');
		}
		
		if ($email == '')
		{
			$missing_fields[] = _AC('email');
		}
		
		if ($guideline_id <= 0)
		{
			$missing_fields[] = _AC('guidelines');
		}
		
		if ($missing_fields)
		{
			$missing_fields = implode(', ', $missing_fields);
			$msg->addError(array('EMPTY_FIELDS', $missing_fields));
		}
		
		if (!$msg->containsErrors())
		{
			/* insert into the db */
			$sql = "INSERT INTO ".TABLE_PREFIX."monitor
			              (title,
			               description,
						   url,
						   period_id,
			               email,
			               user_id,
			               status,
			               guideline_id,
			               show_source,
			               create_date
			               )
			       VALUES ('".$title."',
			               '".$description."',
			               '".$url."',
			               '".$period_id."',
			               '".$email."',
			               '".$user_id."',
			               '".$status."',
			               '".$guideline_id."',
			               '".$show_source."',
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
	 * Update an existing monitor
	 * @access  public
	 * @param   monitor_id
	 *          title
	 *          description
	 *          url
	 *          period_id
	 *          email
	 *          user_id
	 *          status
	 *          guideline_id
	 *          show_source
	 * @return  monitor id, if successful
	 *          false and add error into global var $msg, if unsuccessful
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function Update($monitor_id, $title, $description, $url, $period_id, $email, $status, $guideline_id, $show_source)
	{
		global $addslashes, $msg;

		$missing_fields = array();
		
		$monitor_id = intval($monitor_id);
		$title = $addslashes(trim($title));
		$description = $addslashes(trim($description));
		$url = $addslashes(trim($url));
		$period_id = intval($period_id);
		$email = $addslashes(trim($email));
		$status = intval($status);
		$guideline_id = intval($guideline_id);
		$show_source = intval($show_source);
		
		if ($title == '')
		{
			$missing_fields[] = _AC('title');
		}
		
		if ($url == '')
		{
			$missing_fields[] = _AC('url');
		}
		
		if ($period_id <= 0)
		{
			$missing_fields[] = _AC('period');
		}
		
		if ($email == '')
		{
			$missing_fields[] = _AC('email');
		}
		
		if ($guideline_id <= 0)
		{
			$missing_fields[] = _AC('guidelines');
		}
		
		if ($missing_fields)
		{
			$missing_fields = implode(', ', $missing_fields);
			$msg->addError(array('EMPTY_FIELDS', $missing_fields));
		}
		
		if (!$msg->containsErrors())
		{
			/* update into the db */
			$sql = "UPDATE ".TABLE_PREFIX."monitor
			           SET title = '".$title."',
			               description = '".$description."',
			               url = '".$url."',
			               period_id = '".$period_id."',
			               email = '".$email."',
			               status = '".$status."',
			               guideline_id = '".$guideline_id."',
			               show_source = '".$show_source."',
			               last_update = now()
			        WHERE monitor_id = ".$monitor_id;
			
			$result = $this->execute($sql);
			if (!$result)
			{
				$msg->addError('DB_NOT_UPDATED');
				return false;
			}
			else
			{
				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * delete monitor by given id
	 * @access  public
	 * @param   monitor id
	 * @return  true / false
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function Delete($monitor_id)
	{
		$monitor_id = intval($monitor_id);
		
		// delete monitor
		$sql = 'DELETE FROM '.TABLE_PREFIX.'monitor WHERE monitor_id = '.$monitor_id;
		
		return $this->execute($sql);
	}
	
	/**
	 * Return monitor information by given id
	 * @access  public
	 * @param   monitor id
	 * @return  monitor row
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getMonitorByID($monitor_id)
	{
		$monitor_id = intval($monitor_id);
		
		$sql = 'SELECT * FROM '.TABLE_PREFIX.'monitor WHERE monitor_id='.$monitor_id;
		if ($rows = $this->execute($sql))
		{
			return $rows[0];
		}
	}

	/**
	 * Return monitor information enabled and pending automation
	 * @access  public
	 * @return  monitor rows
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getMonitorsAuto($limit)
	{
		$sql = 'SELECT M.monitor_id, M.URL, M.guideline_id, M.user_id, M.email, M.show_source
				FROM '.TABLE_PREFIX.'monitor M
				INNER JOIN '.TABLE_PREFIX.'monitor_period P ON (P.period_id = M.period_id)
				WHERE M.status = 1
				  AND M.auto_status = 0				
				  AND NOT EXISTS (SELECT MR.result_id 
								  FROM '.TABLE_PREFIX.'monitor_result MR 
								  WHERE MR.monitor_id = M.monitor_id 
								    AND MR.type = 0 
									AND DATE(create_date) > DATE(NOW()) - INTERVAL P.value DAY)
				LIMIT '.$limit;
		if ($rows = $this->execute($sql))
		{
			return $rows;
		} else {
			return false;
		}
	}	

	/**
	 * Return count of monitors pending automation
	 * @access  public
	 * @return  monitor count
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getCountMonitorsAuto()
	{
		$sql = 'SELECT count(M.monitor_id) count
				FROM '.TABLE_PREFIX.'monitor M
				INNER JOIN '.TABLE_PREFIX.'monitor_period P ON (P.period_id = M.period_id)
				WHERE M.status = 1 
				  AND M.auto_status = 0 
				  AND NOT EXISTS (SELECT MR.result_id 
								  FROM '.TABLE_PREFIX.'monitor_result MR 
								  WHERE MR.monitor_id = M.monitor_id 
								    AND MR.type = 0 
									AND DATE(create_date) > DATE(NOW()) - INTERVAL P.value DAY)';
		$rows = $this->execute($sql);
		if (is_array($rows))
		{
			return $rows[0]['count'];
		}
		else
		{
			return false;
		}
	}	
	
	/**
	 * Return monitor title by given id
	 * @access  public
	 * @param   monitor id
	 * @return  title
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getMonitorTitle($monitor_id)
	{
		$monitor_id = intval($monitor_id);
		
		$row = $this->getMonitorByID($monitor_id);
		
		if (!$row) return false;
		
		return $row['title'];
	}
	
	/**
	 * Return count of period
	 * @access  public
	 * @param   period id
	 * @return  count period in use
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function getCountByPeriod($period_id)
	{
		$period_id = intval($period_id);
		
		$sql = 'SELECT COUNT(*) AS count FROM '.TABLE_PREFIX.'monitor WHERE period_id='.$period_id;
		
		$rows = $this->execute($sql);
		if (is_array($rows))
		{
			return $rows[0]['count'];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * update monitor auto_status by given id
	 * @access  public
	 * @param   monitor id and auto_status
	 * @return  monitor id, if successful
	 *          false and add error into global var $msg, if unsuccessful
	 * @author  Aline Bossi Pereira da Silva
	 */
	private function setAuto($monitor_id, $auto_status)
	{
		$monitor_id = intval($monitor_id);
		
		// delete monitor
		$sql = 'UPDATE '.TABLE_PREFIX.'monitor 
		        SET auto_status = '.$auto_status.' 
				WHERE monitor_id = '.$monitor_id;
		
		$result = $this->execute($sql);
		if (!$result)
		{
			$msg->addError('DB_NOT_UPDATED');
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	/**
	 * update monitor auto_status by all failed
	 * @access  public
	 * @return  true, if successful
	 *          false and add error into global var $msg, if unsuccessful
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function setCleanupAutoFail()
	{
		$sql = 'UPDATE '.TABLE_PREFIX.'monitor 
		        SET auto_status = 0 
				WHERE auto_status = 2';
		
		$result = $this->execute($sql);
		if (!$result)
		{
			$msg->addError('DB_NOT_UPDATED');
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * update monitor auto_status to running by given id
	 * @access  public
	 * @param   monitor id
	 * @return  true / false
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function setAutoRun($monitor_id)
	{
		return $this->setAuto($monitor_id, AC_AUTO_MONITOR_RUNNING);
	}	

	/**
	 * update monitor auto_status to fail by given id
	 * @access  public
	 * @param   monitor id
	 * @return  true / false
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function setAutoFail($monitor_id)
	{
		return $this->setAuto($monitor_id, AC_AUTO_MONITOR_FAIL);
	}	
	
	/**
	 * update monitor auto_status to default by given id
	 * @access  public
	 * @param   monitor id
	 * @return  true / false
	 * @author  Aline Bossi Pereira da Silva
	 */
	public function setAutoDefault($monitor_id)
	{
		return $this->setAuto($monitor_id, AC_AUTO_MONITOR_DEFAULT);
	}	
}
?>