<?php
require_once("Rest.inc.php"); // for rest support
/*
PHP REST SQL: A HTTP REST interface to relational databases
written in PHP By Mufaddal Kangsawala 2015
postgresql.php :: PostgreSQL database adapter

Include in Controller of rest api for better use.
or extend in your module or class 
*/

/* $id$ */

/**
 * PHP REST PostgreSQL class
 * PostgreSQL connection class.
 */
class postgresql extends REST {
    
	/**
	 * @var int
	 */
	var $lastInsertPKeys;
	
	/**
	 * @var resource
	 */
    var $lastQueryResultResource;
    
    /**
     * @var resource Database resource
     */
    var $db;
    
    
    /**
     * Connect to the database.
     * @param str[] config
     */
    function connect($config = null) {
		$connString = sprintf(
			'host=%s dbname=%s user=%s password=%s port=%s',
			$config['server'],
			$config['database'],
			$config['username'],
			$config['password'],
            $config['port']
		);
		
        if ($this->db = pg_pconnect($connString)) {
            return TRUE;
	    }
		return FALSE;
    }

    /**
     * Close the database connection.
     */
    function close() {
        pg_close($this->db);
    }
    
    /**
     * Get the columns in a table.
     * @param str table
     * @return resource A resultset resource
     */
    function getColumns($table) {
    	$qs = sprintf('SELECT * FROM information_schema.columns WHERE table_name =\'%s\'', $table);
		return pg_query($qs, $this->db);
    }
    
    /**
     * Get a row from a table.
     * @param str table
     * @param str where
     * @return resource A resultset resource
     */
    function getRow($table, $where) {
        $result = pg_query(sprintf('SELECT * FROM %s WHERE %s', $table, $where));   
    	if ($result) {
	        $this->lastQueryResultResource = $result;
	    }
        return $result;
    }
    
    /**
     * Get all rows in a table.
     * @param str columns The names of the columns to return
     * @param str table
     * @return resource A resultset resource
     */
    function getTable($columns, $table) {
        $result = pg_query(sprintf('SELECT %s FROM %s', $columns, $table));  
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;        
    }
    
    /**
     * Get the rows in a table with where condition.
     * @param str columns The names of the columns to return
     * @param str table
     * @param srt where
     * @return resource A resultset resource
     */
    function getSelectedRC($columns, $table,$where) {
        $result = pg_query(sprintf('SELECT %s FROM %s WHERE %s', $columns, $table,$where));  
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;        
    }
    
    /**
     * Get the rows in a query.
     * @param str query to execute
     * @return resource A resultset resource
     */
    function executeQuery($query) {
        $result = pg_query(sprintf('%s', $query));  
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
        return pg_query('SELECT table_name FROM information_schema.tables WHERE table_schema=\'public\'');   
    }
	
    /**
     * Get the primary keys for the request table.
     * @return str[] The primary key field names
     */
    function getPrimaryKeys($table) {
        $i = 0;
        $primary = NULL;
        do {
		    $query = sprintf('SELECT pg_attribute.attname
		        FROM pg_class, pg_attribute, pg_index
                WHERE pg_class.oid = pg_attribute.attrelid AND
                pg_class.oid = pg_index.indrelid AND
                pg_index.indkey[%d] = pg_attribute.attnum AND
                pg_index.indisprimary = \'t\'
                and relname=\'%s\'',
				$i,
				$table
			);
        	$result = pg_query($query);
            $row = pg_fetch_assoc($result);
            if ($row) {
                $primary[] = $row['attname'];
            } 
            $i++;
        } while ($row);
		
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
        # translate from MySQL syntax :)
        $values = preg_replace('/"/','\'',$values);
        $values = preg_replace('/`/','"',$values); 
        $qs = sprintf('UPDATE %s SET %s WHERE %s', $table, $values, $where);
        $result = pg_query($qs);       
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;
    }
    
    /**
     * Insert a new row.
     * @param str table
     * @param str names
     * @param str values
     * @param str returning field name 
     * @return bool
     */
    function insertRow($table, $names, $values,$returning) {
        # translate from MySQL syntax
		$names = preg_replace('/`/', '"', $names); #backticks r so MySQL-ish! ;)
        $values = preg_replace('/"/', '\'', $values);
        $qs = sprintf(
			'INSERT INTO %s (%s) VALUES (%s) RETURNING ("%s")',
            $table,
			$names,
			$values,
            $returning
		);
        $result = pg_query($qs); #or die(pg_last_error());
		
        $lastInsertPKeys = pg_fetch_row($result);
        $this->lastInsertPKeys = $lastInsertPKeys[0];
		
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;
    }
    
    /**
     * Delete Row from table
     * @param str table
     * @param str where condition
     * @return resource A resultset resource
     */
    function deleteRow($table, $where) {
        $result = pg_query(sprintf('DELETE FROM %s WHERE %s', $table, $where));   
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;
    }
    /**
     * Delete Only One Row from table
     * @param str table
     * @param str where condition
     * @return resource A resultset resource
     */
    function deleteOneRow($table, $where) {
        $result = pg_query(sprintf('DELETE FROM %s WHERE %s LIMIT 1', $table, $where));   
        if ($result) {
            $this->lastQueryResultResource = $result;
        }
        return $result;
    }
    /**
     * Escape a string to be part of the database query.
     * @param str string The string to escape
     * @return str The escaped string
     */
    function escape($string) {
        return pg_escape_string($string);
    }
    
    /**
     * Fetch a row from a query resultset.
     * @param resource resource A resultset resource
     * @return str[] An array of the fields and values from the next row in the resultset
     */
    function row($resource) {
        return pg_fetch_assoc($resource);
    }

    /**
     * The number of rows in a resultset.
     * @param resource resource A resultset resource
     * @return int The number of rows
     */
    function numRows($resource) {
        return pg_num_rows($resource);
    }

    /**
     * The number of rows affected by a query.
     * @return int The number of rows
     */
    function numAffected() {
        return pg_affected_rows($this->lastQueryResultResource);
    }
    
    /**
     * Get the ID of the last inserted record. 
     * @return int The last insert ID ('a/b' in case of multi-field primary key)
     */
    function lastInsertId() {
        return join('/', $this->lastInsertPKeys);
    }
    
}
?>