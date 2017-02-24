<?php

/**
* CDatabase provides a convenient API for database operations.
* TODO: Insert associative array and set it's 'id' with one call.
* TODO: Should return null instead of false, so user could use is_null($item = ...)
*/
class CDatabase extends CComponent
{
    private $_pdo = null;
    
    /**
    * @var string PDO connection driver.
    */
    public $driver = 'mysql';
    
    /**
    * @var string Database host to connect to.
    */
    public $host = '127.0.0.1';
    
    /**
    * @var string Database username.
    */
    public $username = 'root';
    
    /**
    * @var string Database password.
    */
    public $password;
    
    /**
    * @var string Database name.
    */
    public $schema;
    
    /**
    * @var string Database charset.
    */
    public $charset = 'utf8';
    
    /**
    * @return PDO PDO instance.
    */
    public function getPdoInstance()
    {
        if (is_null($this->_pdo))
        {
            if ($this->driver !== 'mysql')
                throw new CException('CDatabase only supports MySQL.');
            
            $dsn = "{$this->driver}:host={$this->host};dbname={$this->schema}";
            $this->_pdo = new PDO($dsn, $this->username, $this->password);
            
            // Throw exceptions on errors
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Emulate prepared statements
            if (constant('PDO::ATTR_EMULATE_PREPARES'))
                $this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            
            // Set database charset
            if (!is_null($this->charset) && in_array($this->driver, array('pgsql', 'mysql', 'mysqli')))
                $this->_pdo->exec('SET NAMES ' . $this->_pdo->quote($this->charset));
        }
        
        return $this->_pdo;
    }
    
    /**
    * Closes database connection.
    */
    public function closeConnection()
    {
        $this->pdo = null;
    }
    
    /**
    * @param string Table name.
    * @return string Quoted table name.
    */
    public function quoteTableName($name)
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
    
    /**
    * @param string Column name.
    * @return string Quoted column name.
    */
    public function quoteColumnName($name)
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
    
    /**
    * Executes the SQL statement and returns all rows.
    * @param string SQL statement to be executed.
    * @param array Input parameters (name => value) for the SQL execution. 
    * @return array All rows of the query result. Each array element is an array representing a row.
    */
    public function queryAll($sql, $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 1);
        return $this->queryInternal($sql, 'fetchAll', PDO::FETCH_ASSOC, $params);
    }
    
    /**
    * Executes the SQL statement and returns all rows. This method sets array
    * indices to match primary keys. First column should contain primary key.
    * @param string SQL statement to be executed.
    * @param array Input parameters (name => value) for the SQL execution. 
    * @return array All rows of the query result. Each array element is an array representing a row.
    */
    public function queryAllWithKeys($sql, $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 1);
        $result = $this->queryInternal($sql, 'fetchAll', PDO::FETCH_ASSOC, $params);
        $return = array(); foreach ($result as $row) $return[reset($row)] = $row;
        return $return;
    }
    
    /**
    * Executes the SQL statement and returns the first row of the result.
    * @param string SQL statement to be executed.
    * @param array Input parameters (name => value) for the SQL execution.
    * @return mixed The first row (in terms of an array) of the query result, false if no result.
    */
    public function queryRow($sql, $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 1);
        return $this->queryInternal($sql, 'fetch', PDO::FETCH_ASSOC, $params);
    }
    
    /**
    * Executes the SQL statement and returns the value of the first column in the first row of data.
    * @param string SQL statement to be executed.
    * @param array Input parameters (name => value) for the SQL execution. 
    * @return mixed The value of the first column in the first row of the query result. False is returned if there is no value.
    */
    public function queryScalar($sql, $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 1);
        $result = $this->queryInternal($sql, 'fetchColumn', 0, $params);
        if (is_resource($result) && get_resource_type($result) === 'stream')
            return stream_get_contents($result);
        else
            return $result;
    }
    
    /**
    * TODO: Fix this method, it doesn't work.
    * Executes the SQL statement and returns the first column of the result.
    * The column returned will contain the first element in each row of result.
    * @param string SQL statement to be executed.
    * @param array $params input parameters (name=>value) for the SQL execution.
    * @return array the first column of the query result. Empty array if no result.
    */
    public function queryColumn($sql, $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 1);
        return $this->queryInternal($sql, 'fetchAll', array(PDO::FETCH_COLUMN, 0), $params);
    }
    
    /**
    * @param string SQL statement to be executed.
    * @param string Method of PDOStatement to be called.
    * @param mixed Parameters to be passed to the method.
    * @param array Input parameters (name=>value) for the SQL execution.
    * @return mixed The method execution result.
    */
    private function queryInternal($sql, $method, $mode, $params = array())
    {
        $statement = $this->getPdoInstance()->prepare($sql);
        
        try
        {
            if ($params === array())
                $statement->execute();
            else
                $statement->execute($params);
            
            // This doesn't work: $statement->setFetchMode($mode);
            call_user_func_array(array($statement, 'setFetchMode'), (array)$mode);
            $result = $statement->$method();
            $statement->closeCursor();
            
            return $result;
        }
        catch (PDOException $e)
        {
            throw new CException('[DB] ' . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
    * Executes the SQL non-query statement.
    * @param array Input parameters (name=>value) for the SQL execution.
    * @return integer Number of rows affected by the execution.
    * @throws CException execution failed.
    */
    public function execute($sql, $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 1);
        
        $statement = $this->getPdoInstance()->prepare($sql);
        
        try
        {
            $statement->execute($params);
            return $statement->rowCount();
        }
        catch (PDOException $e)
        {
            throw new CException('[DB] ' . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
    * Returns the row from specified table.
    * @param string The table that new rows will be read from.
    * @param mixed The conditions that will be put in the WHERE part.
    * @param array The parameters to be bound to the query.
    */
    public function select($table, $where = '', $params = array())
    {
        $sql = 'SELECT * FROM ' . $this->quoteTableName($table);
        if ($where != '') $sql .= ' WHERE ' . $where;
        return $this->queryInternal($sql, 'fetch', PDO::FETCH_ASSOC, $params);
    }
    
    /**
    * Creates and executes an INSERT SQL statement.
    * @param string The table that new rows will be inserted into.
    * @param array The column data (name=>value) to be inserted into the table.
    * @return integer The last inserted record ID.
    */
    public function insert($table, $values)
    {
        $params = array();
        $names = array();
        $placeholders = array();
        
        foreach ($values as $name => $value)
        {
            $names[] = $this->quoteColumnName($name);
            $placeholders[] = ':' . $name;
            $params[':' . $name] = $value;
        }
        
        $sql = 'INSERT INTO ' . $this->quoteTableName($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';
        
        $result = $this->execute($sql, $params);
        return $result === 1 ? $this->getPdoInstance()->lastInsertId() : false;
    }
    
    /**
    * Creates and executes an UPDATE SQL statement.
    * @param string The table to be updated.
    * @param array The column data (name => value) to be updated.
    * @param mixed The conditions that will be put in the WHERE part.
    * @param array The parameters to be bound to the query.
    * @return integer Number of rows affected by the execution.
    */
    public function update($table, $values, $where = '', $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 3);
        
        $lines = array();
        
        foreach ($values as $name => $value)
        {
            $lines[] = $this->quoteColumnName($name) . ' = :' . $name;
            $params[':' . $name] = $value;
        }
        
        $sql = 'UPDATE ' . $this->quoteTableName($table) . ' SET ' . implode(', ', $lines);
        if ($where != '') $sql .= ' WHERE ' . $where;
        return $this->execute($sql, $params);
    }
    
    /**
    * Creates and executes a DELETE SQL statement.
    * @param string The table where the data will be deleted from.
    * @param string The WHERE part of the stamement.
    * @param array The parameters to be bound to the query.
    * @return integer Number of rows affected by the execution.
    */
    public function delete($table, $where = '', $params = array())
    {
        if (!is_array($params)) $params = array_slice(func_get_args(), 2);
        $sql = 'DELETE FROM ' . $this->quoteTableName($table);
        if ($where != '') $sql .= ' WHERE ' . $where;
        return $this->execute($sql, $params);
    }
    
    /**
    * Returns the last insert ID.
    * @return mixed Last insert ID.
    */
    public function getLastInsertId()
    {
        return $this->getPdoInstance()->lastInsertId();
    }
}