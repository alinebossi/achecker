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
* Root data access object
* Each table has a DAO class, all inherits from this class
* @access	public
* @author	Cindy Qi Li
* @package	DAO
*/

class DAO {

	// private
	private $db;     // global database connection
	
	function DAO()
	{
		$this->db = null;
		if (!isset($this->db))
		{			
			$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, FALSE, MYSQL_CLIENT_COMPRESS);
			if (!$this->db) {
				$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, TRUE, MYSQL_CLIENT_COMPRESS);
				if (!$this->db) {
					die('[class]'.get_called_class().'[construtor] Unable to connect to db. [error] '.mysql_error());
				}
			}
			
			if (!mysql_ping($this->db)) {
				mysql_close($this->db);
				
				$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, TRUE, MYSQL_CLIENT_COMPRESS);
				if (!$this->db) {
					die('[class]'.get_called_class().'[construtor] Unable to connect to db 2nd attempt. [error] '.mysql_error());
				}
			}
			
			if (!mysql_select_db(DB_NAME, $this->db)) {				
				mysql_close($this->db);
				
				$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, TRUE, MYSQL_CLIENT_COMPRESS);
				if (!$this->db) {
					die('[class]'.get_called_class().'[construtor] Unable to connect to db 3rd attempt. [error] '.mysql_error());
				}
				
				if (!mysql_select_db(DB_NAME, $this->db)) {
					die('[class]'.get_called_class().'[construtor] DB connection established, but database "'.DB_NAME.'" cannot be selected. [error] '.mysql_error());
				}
			}
		}
	}
	
	/**
	* Execute SQL
	* @access  protected
	* @param   $sql : SQL statment to be executed
	* @return  $rows: for 'select' sql, return retrived rows, 
	*          true:  for non-select sql
	*          false: if fail
	* @author  Cindy Qi Li
	*/
	function execute($sql)
	{
		$sql = trim($sql);
		$sql = trim($sql);
				
		if (!mysql_ping($this->db)) {
			mysql_close($this->db);
			
			$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, FALSE, MYSQL_CLIENT_COMPRESS);
			if (!$this->db) {
				$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, TRUE, MYSQL_CLIENT_COMPRESS);
				if (!$this->db) {
					die('[class]'.get_called_class().'[execute] Unable to connect to db. [error] '.mysql_error());
				}
			}
			
			if (!mysql_ping($this->db)) {
				mysql_close($this->db);
				
				$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, TRUE, MYSQL_CLIENT_COMPRESS);
				if (!$this->db) {
					die('[class]'.get_called_class().'[execute] Unable to connect to db 2nd attempt. [error] '.mysql_error());
				}
			}			
			
			
			if (!mysql_select_db(DB_NAME, $this->db)) {				
				mysql_close($this->db);
				
				$this->db = mysql_connect(DB_HOST . ':' . DB_PORT, DB_USER, DB_PASSWORD, TRUE, MYSQL_CLIENT_COMPRESS);
				if (!$this->db) {
					die('[class]'.get_called_class().'[execute] Unable to connect to db 3rd attempt. [error] '.mysql_error());
				}
				
				if (!mysql_select_db(DB_NAME, $this->db)) {
					die('[class]'.get_called_class().'[execute] DB connection established, but database "'.DB_NAME.'" cannot be selected. [error] '.mysql_error());
				}
			}
		}
		
		$result = mysql_query($sql, $this->db) or die($sql . "<br />". mysql_error());

		// Deal with "select" statement: return false if no row is returned, otherwise, return an array
		if ($result !== true && $result !== false) {
			$rows = false;
			
			while ($row = mysql_fetch_assoc($result)){
				if (!$rows) $rows = array();
				
			    $rows[] = $row;
			}
			mysql_free_result($result);
			return $rows;
		}
		return true;
	}

}
?>