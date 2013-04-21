<?php
define ("_UNBUFFERED_THREAD", 100);
class Database {
      var $strHost="localhost",
    $strUser      ="root",
    $strPasswd    ="spiru", 
    $strDatabase  ="rasp";
  
  var $debug = 0;
  var $Record = array();
 
	var $ptrDB = array();
	var $handlerDB = array();
	var $resultDB = array(array());

	var $iPtr = 0;
	var $iResult = 0;

	var $nLastID = array();
	var $nNumRows = array(array());
	var $nAffectedRows = array(array());

	var $strUnbufferedQuery;
	var $iThread;
	var $queryBeginTime;
	var $queryMaxTime;

	var $bTime = true;
	/**
	 * Connect to MySQL.
	 *
	 * @param string $strHost - host to connect to
	 * @param string $strUser - mysql user
	 * @param string $strPasswd - mysql passwd
	 * @param string $strDatabase - database
	 * @access public
	 * @return void
	 */
function Database()
 {
  $this->ptrDB[$this->iPtr] = @mysql_connect($this->strHost, $this->strUser, $this->strPasswd)
  or $this->Err("Couldnt connect to MySQL server", mysql_error(), mysql_errno());

  if (!@mysql_select_db($this->strDatabase, $this->ptrDB[$this->iPtr]))
  $this->Err("Couldnt open database (Link id: " . $this->iPtr . ")", mysql_error(), mysql_errno());
}
	function GetLastID()
	{
		return mysql_insert_id();
	}
	function GetNumRows()
	{
		return $this->nNumRows[$this->iPtr][$this->iResult];
	}
	/**
	 * Activate another link identifier and resource result.
	 *
	 * @param integer $nPtr - link identifier
	 * @param integer $nResult - resource result
	 * @access public
	 * @return void
	 */
	function ChangePtr($nPtr, $nResult) {
		$this->iPtr = $nPtr;
		$this->iResult = $nResult;
	}
	/**
	 * Select another database in link identifier.
	 *
	 * @param string $strDatabase - database name
	 * @param integer $iPtr - link identifier
	 * @access public
	 * @return void
	 */
	function ReSelectDB($iPtr = null) {
		if (!@mysql_select_db($this->strDatabase, $this->ptrDB[(($iPtr)?$iPtr:$this->iPtr)]))
			$this->Err("Couldnt open database (Link id: " . (($iPtr) ? $iPtr : $this->iPtr) . ")", mysql_error(), mysql_errno());
	}
	/**
	 * Outputs info about mysql server.
	 *
	 * @access public
	 * @return void
	 */
	function DBInfo() {
		printf("Host info: %s<br>\r\n", mysql_get_host_info($this->ptrDB[$this->iPtr]));
		printf("Client info: %s<br>\r\n", mysql_get_client_info());
		printf("Server version: %s<br>\r\n", mysql_get_server_info($this->ptrDB[$this->iPtr]));
	}
	/**
	 * Run query.
	 *
	 * @param string $strFields - fields to select
	 * @param string $strTables - tables to select
	 * @param string $strWhere - filter in query
	 * @param string $strGroupBy - group by
	 * @param string $strOrderBy - order by
	 * @param string $strLimit - num results in query
	 * @access public
	 * @return $strSQL
	 */
	function Select($strFields, $strTables, $strWhere = null, $strGroupBy = null, $strOrderBy = null, $strLimit = null) {
		$strSQL = "select " . $strFields . " from " . $strTables;

		$strSQL .= (!empty($strWhere)) ? " where " .$strWhere : null;
		$strSQL .= (!empty($strGroupBy)) ? " group by " . $strGroupBy : null;
		$strSQL .= (!empty($strOrderBy)) ? " order by " . $strOrderBy : null;
		$strSQL .= (!empty($strLimit)) ? " limit " . $strLimit : null;

		$this->resultDB[$this->iPtr][$this->iResult] = @mysql_query($strSQL, $this->ptrDB[$this->iPtr])
			or $this->Err($strSQL, mysql_error(), mysql_errno());

		$this->nNumRows[$this->iPtr][$this->iResult] = @mysql_num_rows($this->resultDB[$this->iPtr][$this->iResult]);
		return $strSQL;
	}
	/**
	 * Run query unbeffered.
	 *
	 * @param integer $cTimeLimit - max execution time to run query
	 * @param string $strFields - fields to select
	 * @param string $strTables - tables to select
	 * @param string $strWhere - filter in query
	 * @param string $strGroupBy - group by
	 * @param string $strOrderBy - order by
	 * @param string $strLimit - num results in query
	 * @access public
	 * @return $strSQL
	 */
	function SelectUnbufferedQuery($cTimeLimit = 60, $strFields, $strTables, $strWhere = null, $strGroupBy = null, $strOrderBy = null, $strLimit = null) {
		$this->ptrDB[_UNBUFFERED_THREAD] = @mysql_connect($this->strHost, $this->strUser, $this->strPasswd, true)
			or $this->Err("Couldnt connect to MySQL server", mysql_error(), mysql_errno());

		$strSQL = "select " . $strFields . " from " . $strTables;

		$strSQL .= (!empty($strWhere)) ? " where " .$strWhere : null;
		$strSQL .= (!empty($strGroupBy)) ? " group by " . $strGroupBy : null;
		$strSQL .= (!empty($strOrderBy)) ? " order by " . $strOrderBy : null;
		$strSQL .= (!empty($strLimit)) ? " limit " . $strLimit : null;

		$this->strUnbufferedQuery = $strSQL;
		$this->queryMaxTime = $cTimeLimit;
		$this->queryBeginTime = time();

		$this->resultDB[$this->iPtr][$this->iResult] = @mysql_query($strSQL, $this->ptrDB[$this->iPtr])
			or $this->Err($strSQL, mysql_error(), mysql_errno());

		$this->iThread = mysql_thread_id($this->ptrDB[$this->iPtr]);
		return $strSQL;
	}
	/**
	 * Execute   query
	 *
	 * @param string $strSQL - query
	 * @access public
	 * @return $strSQL
	 */
	function Execute($strSQL) {
		$this->resultDB[$this->iPtr][$this->iResult] = @mysql_query($strSQL, $this->ptrDB[$this->iPtr])
			or $this->Err($strSQL, mysql_error(), mysql_errno());

		$this->nNumRows[$this->iPtr][$this->iResult] = @mysql_num_rows($this->resultDB[$this->iPtr][$this->iResult]);
		return $strSQL;
	}
	/**
	 * Update row(s) in table.
	 *
	 * @param string $strTable - table
	 * @param string/array $arrNewValues - array ("id = 5", "name = 'adam'")
	 * @param string/array $strWhere - filter in query
	 * @access public
	 * @return $strSQL
	 */
	function Update($strTable, $arrNewValues, $strWhere = null) {
		$strSQL = "update " . $strTable . " set " . ((is_array($arrNewValues)) ? implode (", ", $arrNewValues) : $arrNewValues);
		$strSQL .= (!empty($strWhere)) ? " where " . $strWhere : null;

		@mysql_query($strSQL, $this->ptrDB[$this->iPtr])
			or $this->Err($strSQL, mysql_error(), mysql_errno());

		$this->nAffectedRows[$this->iPtr][$this->iResult] = @mysql_affected_rows($this->resultDB[$this->iPtr][$this->iResult]);
		return $strSQL;
	}
	/**
	 * Insert row.
	 *
	 * @param string $strTable - table
	 * @param string/array $arrFields - array (id, name, msg)
	 * @param string/array $arrFields - array ($id, $name, "message")
	 * @access public
	 * @return $strSQL
	 */
	function Insert($strTable, $arrFields, $arrValues) {
		$strSQL = "insert into " . $strTable;

		$strFields = (is_array($arrFields)) ? "(" . implode(", ", $arrFields) . ")" : $arrFields;
		$strValues = (is_array($arrValues)) ? "('" . implode("', '", $arrValues) . "')" : $arrValues;

		$strValues = str_replace("'now()'", "now()", $strValues);
		$strValues = str_replace("'null'", "null", $strValues);

		$strSQL .= " " . $strFields . " values " . $strValues;

		@mysql_query($strSQL, $this->ptrDB[$this->iPtr])
			or $this->Err($strSQL, mysql_error(), mysql_errno());

		$this->nAffectedRows[$this->iPtr][$this->iResult] = @mysql_affected_rows($this->resultDB[$this->iPtr][$this->iResult]);
		$this->nLastID[$this->iPtr] = mysql_insert_id();
		return $strSQL;
	}
	/**
	 * Delete row(s).
	 *
	 * @param string $strTable - table
	 * @param string $strWhere - filter in query
	 * @access public
	 * @return $strSQL
	 */
	function Delete($strTable, $strWhere = null) {
		$strSQL = "detele from " . $strTable;

		$strSQL .= (!empty($strWhere)) ? " where " . $strWhere : null;

		@mysql_query($strSQL, $this->ptrDB[$this->iPtr])
			or $this->Err($strSQL, mysql_error(), mysql_errno());

		$this->nAffectedRows[$this->iPtr][$this->iResult] = @mysql_affected_rows($this->resultDB[$this->iPtr][$this->iResult]);
		return $strSQL;
	}
	/**
	 * Returns an object with properties that correspond to the fetched row.
	 *
	 * @access public
	 * @return object
	 */
	function GetObject() {
		return mysql_fetch_object($this->resultDB[$this->iPtr][$this->iResult]);
	}	
	/**
	 * GetRecord = fetch a result row as an associative array, a numeric array, or both.
	 *
	 * @access public
	 * @return array
	 */
	function GetRecord() {
		$this->Record=mysql_fetch_array($this->resultDB[$this->iPtr][$this->iResult]);
		return $this->Record;
	}
	/**
	 * Fetch a result row as an associative array.
	 *
	 * @access public
	 * @return array
	 */
	function GetAssoc() {
    	$this->Record=mysql_fetch_assoc($this->resultDB[$this->iPtr][$this->iResult]);
		return $this->Record;
	}
	/**
	 * Get column information from a result and return as an object.
	 *
	 * @param integer $numFields - Number of fields to return
	 * @access public
	 * @return object
	 */
	function GetFields($numFields = null) {
		return mysql_fetch_field($this->resultDB[$this->iPtr][$this->iResult], (($numFields) ? $numFields : mysql_num_fields($this->resultDB[$this->iPtr][$this->iResult])));
	}
	/**
	 * Move internal result pointer to a giving row, dont use with unbuffered result.
	 *
	 * @param integer $nRow - Moves pointer x rows
	 * @access public
	 * @return true/false
	 */
	function DataSeek($nRow) {
		return (mysql_data_seek($this->resultDB[$this->iPtr][$this->iResult], $nRow) ? true : false);
	}
	
	
	function Escape($str)
	{
		 return mysql_real_escape_string($str);
	}
	 
	/**
	 * Returns an object with properties that correspond to the fetched row. Kills thread if executions time exceeds.
	 *
	 * @access public
	 * @return object/false
	 */ 
	function GetUnbufferedObject() {
		if (!$this->bTime = (((time() - $this->queryBeginTime) < $this->queryMaxTime) ? true : false)) {
			mysql_query ("kill " . $this->iThread, $this->ptrDB[_UNBUFFERED_THREAD]);
			return false;					
		} else
			return mysql_fetch_object($this->resultDB[$this->iPtr][$this->iResult]);
	}
	/**
	 * Fetch a result row as an associative array, a numeric array, or both. Kills thread if executions time exceeds.
	 *
	 * @access public
	 * @return array/false
	 */
	function GetUnbufferedArray() {
		if (!$this->bTime = (((time() - $this->queryBeginTime) < $this->queryMaxTime) ? true : false)) {
			mysql_query ("kill " . $this->iThread, $this->ptrDB[_UNBUFFERED_THREAD]);
			return false;					
		} else
			return mysql_fetch_array($this->resultDB[$this->iPtr][$this->iResult]);
	}
	/**
	 * Free result memory.
	 *
	 * @access public
	 * @return void
	 */
	function FreeDB() {
		@mysql_free_result($this->resultDB[$this->iPtr][$this->iResult])
			or $this->Err("Couldnt free memory", mysql_error(), mysql_errno());
	}
	/**
	 * Close MySQL connection.
	 *
	 * @access public
	 * @return void
	 */
	function CloseDB() 
	{
		mysql_close($this->ptrDB[$this->iPtr]);
	}
	/**
	 * Output errors.
	 *
	 * @access private
	 * @return void
	 */
	function Err($strMsg, $strMyErr, $strMyErrno) 
	{	
		if($this->debug==1)
		{
			printf("Message: %s<br>\r\n", $strMsg);
			printf("Error: %s<br>\r\n", $strMyErr);
			printf("Errno: %s<br>\r\n", $strMyErrno);
			//exit();
		}	
	//header("location: /");	
	}
}

?>
