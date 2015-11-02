<?php
/**
 * For logging errors and waring
 */
//include_once('logger.php');
/*
PHP REST SQL: A HTTP REST interface to relational databases
written in PHP By Mufaddal Kangsawala 2015

mysql.php :: MySQL database adapter

/* $id$ */

/**
 * PHP REST MySQL class
 * MySQL connection class.
 */
class mysql {   
    
    /**
     * @var resource Database resource
     */
    var $db;
    var $status;
    /**
     * Connect to the database.
     * @param str[] config
     */
    function connect($config) {
        if ($this->db = @mysqli_connect(
			'p:'.$config['server'],
			$config['username'],
			$config['password']
		)) {
			if ($this->select_db($config['database'])) {
			     $this->status = $config['status'];
				return TRUE;
			}
        }
        return FALSE;
    }

    /**
     * Close the database connection.
     */
    function close() {
        mysqli_close($this->db);
    }
    
    /**
     * Use a database
     */
    function select_db($database) {
        if (mysqli_select_db($this->db, $database)) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Get the columns in a table.
     * @param str table
     * @return resource A resultset resource
     */
    function getColumns($table) { 
        return mysqli_query($this->db, sprintf('SHOW COLUMNS FROM %s', $table));
    }
    
    /**
     * Get a row from a table.
     * @param str table
     * @param str where
     * @return resource A resultset resource
     */
    function getRow($table, $where) {
        return mysqli_query($this->db, sprintf('SELECT * FROM %s WHERE %s', $table, $where));
    }
    
    /**
     * Get the rows in a table with where condition.
     * @param str columns The names of the columns to return
     * @param str table
     * @param str where (optional default blank) 
     * @package str other (optional for clauses default null)
     * @return resource A resultset resource
     */
    function getSelectedRC($columns, $table,$where = "", $other = null) {
        if(empty($where))
        {
            $result = mysqli_query($this->db,sprintf('SELECT %s FROM %s %s', $columns, $table, $other));
        }
        else
        {
            $result = mysqli_query($this->db,sprintf('SELECT %s FROM %s WHERE %s %s', $columns, $table, $where, $other));
        }   
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;        
    }
    /**
     * Get the rows in a table.
     * @param str primary The names of the primary columns to return
     * @param str table
     * @return resource A resultset resource
     */
    function getTable($primary, $table) {
        return mysqli_query($this->db, sprintf('SELECT %s FROM %s', $primary, $table));
    }
    
    /**
     * Get the rows in a query.
     * @param str query to execute
     * @return resource A resultset resource
     */
    function executeQuery($query) {
        $result = mysqli_query($this->db, sprintf('%s', $query));
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;        
    }
    /**
     * Get the tables in a database.
     * @return resource A resultset resource
     */
    function getDatabase() {
        return mysqli_query($this->db, 'SHOW TABLES');
    }

    /**
     * Get the primary keys for the request table.
     * @return str[] The primary key field names
     */
    function getPrimaryKeys($table) {
        $resource = $this->getColumns($table);
        $primary = NULL;
        if ($resource) {
            while ($row = $this->row($resource)) {
                if ($row['Key'] == 'PRI') {
                    $primary[] = $row['Field'];
                }
            }
        }
        return $primary;
    }
    
    /**
     * Update a row.
     * @param str table
     * @param str values
     * @param str where
     * @return bool
     */
    function updateRow($table, $values, $where) {
        return mysqli_query($this->db, sprintf('UPDATE %s SET %s WHERE %s', $table, $values, $where));
    }
    /**
     * Update a row.
     * @param str table
     * @param array values array key =>value pair
     * @param str where
     * @return bool
     */
    function updateRowArray($table, $values, $where) {
        $sets = array();
		foreach($values as $column => $value)
		{
			 $sets[] = "$column = '".$this->escape($value)."'";
		}
        $set_data = implode(', ', $sets);
        return mysqli_query($this->db, sprintf('UPDATE %s SET %s WHERE %s', $table, $set_data, $where));
    }
    /**
     * Insert a new row.
     * @param str table
     * @param str names
     * @param str values
     * @return bool
     */
    function insertRow($table, $names, $values) {
        return mysqli_query($this->db, sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, $names, $values));
    }
    /**
     * Insert a new row by set insert query.
     * @param str table
     * @param str names
     * @return bool
     */
    function insertRowSet($table, $setstring) {
        return mysqli_query($this->db, sprintf('INSERT INTO %s SET %s', $table, $setstring));
    }
    /**
     * Insert a new row by set insert query.
     * @param str table
     * @param array column value
     * @return bool
     */
    function insertRowSetArray($table, $setarray) {
        $sets = array();
		foreach($setarray as $column => $value)
		{
			 $sets[] = "$column = '".$this->escape($value)."'";
		}
        $set_data = implode(', ', $sets);
        return mysqli_query($this->db, sprintf('INSERT INTO %s SET %s', $table, $set_data));
    }
    /**
     * Insert mulitple rows by set insert query.
     * @param str table
     * @param array column value
     * @return bool (with roll back operation)
     */
    function insertBatch($table, $setarray) {
        $test ="";
        $this->db->autocommit(FALSE);
        $sets = array();
        for($loop = 0; $loop < count($setarray); $loop++)
        {
    		foreach($setarray[$loop] as $column => $value)
    		{
    			 $sets[] = "$column = '".mysql_real_escape_string($value)."'";
    		}
            $set_data = implode(', ', $sets);
            $result = mysqli_query($this->db, sprintf('INSERT INTO %s SET %s', $table, $set_data));
            $sets = null;
            $test .= " ".sprintf('INSERT INTO %s SET %s', $table, $set_data);
        }
        $this->db->autocommit(TRUE);
        if($result == true)
        {
            $this->db->commit();
            return true;
        }
        else
        {
            $this->db->rollBack();
            return false;
        }
    }
    /**
     * Get the columns in a table.
     * @param str table
     * @return resource A resultset resource
     */
    function deleteRow($table, $where) {
        return mysqli_query($this->db, sprintf('DELETE FROM %s WHERE %s', $table, $where));
    }
    
    /**
     * Escape a string to be part of the database query.
     * @param str string The string to escape
     * @return str The escaped string
     */
    function escape($string) {
        return mysqli_escape_string($this->db, $string); 
    }
    
    /**
     * Fetch a row from a query resultset.
     * @param resource resource A resultset resource
     * @return str[] An array of the fields and values from the next row in the resultset
     */
    function row($resource) {
        return mysqli_fetch_assoc($resource);
    }

    /**
     * The number of rows in a resultset.
     * @param resource resource A resultset resource
     * @return int The number of rows
     */
    function numRows($resource) {
        return mysqli_num_rows($resource);
    }

    /**
     * The number of rows affected by a query.
     * @return int The number of rows
     */
    function numAffected() {
        return mysqli_affected_rows($this->db);
    }
    
    /**
     * Get the ID of the last inserted record. 
     * @return int The last insert ID
     */
    function lastInsertId() {
        return mysqli_insert_id($this->db);
    }
    /**
     * Get the Error if occured while query execution 
     * @return str error
     */
    function error() {
        if($this->status == 1 || $this->status == 1)
        {
           // Logger::error_sql(mysqli_error($this->db));
            return mysqli_error($this->db);
        }
        else
        {
            //Logger::error_sql(mysqli_error($this->db));
            return 'Opps.. Some Database error occured Please try again';
        }
    }
    
}
?>
