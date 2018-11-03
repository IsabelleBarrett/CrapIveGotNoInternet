<?
//--------------------------------------//
// inc_db.php ver 2.0					//
// James Wilkes							//
// Created: 12/08/2004					//
//--------------------------------------//

//------------------------------------------------------//
// Contains base functions for accessing the database

//	CLASS TO CREATE A CONNECTION TO THE DB
//	doesn't actually get any data from the db - no real reason to use this class alone
//	if you want to perform any DB operations, use the class DB (below) which extends this one.

// get standard functions
include "functions.php";
// get the global variables
include "global_vars.php";

class DB_CONNECT
{


// *************************************************************************************************
	var $Link_ID = 0;  // Result of mysql_connect().
	var $Query_ID = 0;  // Result of most recent mysql_query().
	var $Record = array();  // current mysql_fetch_array()-result.
	var $Row;           // current row number.

	var $Errno= 0;  // error state of query...
	var $Error= "";

	// setup database variables
	// e.g. var $Host= "212.69.194.236:3306";
	var $Host = "localhost";
	// var $Database = "homeserve_demo";
	var $Database = "thopea_ei";
	/*
	var $User = "root";
	var $Password = "";
	*/
	var $User = "thopea_ei";
	var $Password = "ihavenointernet";


	function halt($msg)
	{
		$this->Errno;
		$this->Error;
		die($msg);
	}

	function connect()
	{
		if ( 0 == $this->Link_ID )
		{
			$this->Link_ID=mysql_connect($this->Host, $this->User, $this->Password);
			if (!$this->Link_ID)
			{
				$this->halt("Couldn't connect to MySQL");
			}
			if (!mysql_query(sprintf("use %s",$this->Database),$this->Link_ID))
			{
				$this->halt("Couldn't select database".$this->Database);
			}
		}
	}
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//
//	MAIN DATABASE CLASS
//
//	Use this to query, insert, update and delete from the DB
//

class DB extends DB_CONNECT
{
	function query($Query_String)
	{
		$this->connect();
		$this->Query_ID = mysql_query($Query_String,$this->Link_ID);
		$this->Row = 0;
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();
		if (!$this->Query_ID)
		{
			$this->halt("Invalid SQL: ".$Query_String);
		}
		return $this->Query_ID;
	}

	function next_record()
	{
		$this->Record = mysql_fetch_array($this->Query_ID);
		$this->Row += 1;
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();

		$stat = is_array($this->Record);
		if (!$stat)
		{
			mysql_free_result($this->Query_ID);
			$this->Query_ID = 0;
		}
		return $stat;
	}

	function getval($query,$fieldname)
	{
		$this->query($query);
		while($this->next_record()):
			$varname=$this->Record[$fieldname];
		endwhile;
		return $varname;
	}

	function seek($pos)
	{
		$status = mysql_data_seek($this->Query_ID, $pos);
		if ($status) $this->Row = $pos;
		return;
	}

	function num_rows()
	{
		return mysql_num_rows($this->Query_ID);
	}

	function num_fields()
	{
		return mysql_num_fields($this->Query_ID);
	}

	function f($Name)
	{
		$temp = $this->Record[$Name];
		return stripslashes($temp);
	}

	function p($Name)
	{
		$temp = $this->Record[$Name];
		echo stripslashes($temp);
	}

	function affected_rows()
	{
		return @mysql_affected_rows($this->Link_ID);
	}
}

include "custom_functions.php";
?>
