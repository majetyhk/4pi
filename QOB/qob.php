<?php
//============================================================



// File name   : qob.php
// Version     : 1.0
// Begin       : 19-07-2014
// Last Update : 30-07-2014
// Author(s)   : Battinoju Sai Kumar - battinojusaikumar@gmail.com



// -------------------------------------------------------------------
// Copyright (C) 2014 Battinoju Sai Kumar

// This file is part of BSK library.

// QOB is free software: you can redistribute it and/or modify it for your own purposes.

// QOB is distributed in the hope that it will be useful,
// and help in making secure applications by using the latest
// mysqli secure features



// Description :
//   This is a PHP class for exchanging data with MySQL server in 
//   an object oriented and secure way. Usage of this class prevents
//   MySQL injections to a greater extent, in fact all.



// Main features:
//  * no external libraries are required for the basic functions
//  * follows MySQLi standards
//  * inbuilt support for MySQLi prepare statements to prevent sql injections
//  * easier to use
//  * automatic error logging

//============================================================+









//QOB configuration file
require("qobConfig.php");

//QOB helper functions support
require("helper.php");









/*********************************************************************
 * Class QoB - this class is used to achieve all the database operations effectively and securely as 
               it uses the mysqli's object oriented implementation and prepared statements to prevent
			   MySQL injection


 * data members: affectedRows - Number of affected rows after insert or delete operations
                 error - to store errors in executing the queries at any stage
				 result - to store result after select operations
				 conn - mysqli connection object
				 query - query which is being executed
				 stmt - mysqli stmt object generated after binding the results
				 bindVals - values to be bonded with the query
				 numRows - number of rows selected in a select query
				 getResult - result parameter responsible to select rows one by one

 * member functions: listed below

 * Bugs: None

 * To be done: nothing
*********************************************************************/
class QoB
  {
    /////////////DATA MEMBERS////////////////
    public $affectedRows = 0;
	public $error = "";
	public $result = 0;
	private $conn = 0;
	private $query = "";
	private $stmt = "";
	private $bindVals = "";
	public $numRows = 0;
	private $getResult = 0;
    /////////////DATA MEMBERS////////////////









    /////////////MEMBER FUNCTIONS////////////////
    /*********************************************************************
     * construct - this is the constructor for the above defined class
    
     * Returns: nothing
    
     * Arguments: host - host name
	              user - MySQL username
				  password - MySQL password
				  db - MySQL database name
    
     * Method: establishes MySQLi connection
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
	public function __construct($host = HOST, $user = USER, $password = PASSWORD, $db = DB)
	  {
        //create the new connection object
        $this->conn = @new mysqli($host, $user, $password, $db);
        
        //if we could not connect to database
        if($this->conn->connect_errno)
          {
		    $this->error = $this->conn->err_no . ' - ' .$this->conn->connect_error;
            //generate current time stamp
        	$time = timeStamp();
        	
        	//prepare error message in a particular format
        	$errorMessage  = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), $this->conn->connect_errno, $this->conn->connect_error);
        	
        	//insert into the log file for further reference
        	insertLog($errorMessage);
        
        	//trigger error onto the page
        	trigger_error('Database connection failed: '  . $this->conn->connect_error, E_USER_ERROR);
          }//end of if
	  }/* End of constructor */








    /*********************************************************************
     * destruct - this is the destructor for QoB
    
     * Returns: nothing
    
     * Arguments: none
    
     * Method: destroys the connection variables
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
	public function __destruct()
	  {
	    //to close MySQLi connection
	    $this->close();
	  }/* End of destructor */









    /*********************************************************************
     * reset - this function is used to reset all temporary variables
    
     * Returns: nothing
    
     * Arguments: none
    
     * Method: unset all the temporary parameters
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
	private function reset()
	  {
	    //reset all the variables associated with previous queries
	    $this->error = "";
		
		$this->affectedRows = 0;
		
		$this->result = "";
		
		$this->numRows=0;
		
		$this->bindVals="";
	  }/* End of reset */

	  
	  
	  
	  
	  
	  
	  
	  
    /*********************************************************************
     * close - this function is used to close mysql connections
    
     * Returns: nothing
    
     * Arguments: none
    
     * Method: we use the mysqli close functions to close the connection object
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
	public function close()
	  {
	    //close if any MySQLi statement variables exist
	    if($this->stmt)
	      $this->stmt->close();
		
		//close MySQLi connection
	    $this->conn->close();
	  }/* End of close */









    /*********************************************************************
     * prepareQuery - this function is used to prepare an MySQL query
    
     * Returns: true or false
    
     * Arguments: none
    
     * Method: we use the mysqli prepare function to prepare things and return the object
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    private function prepareQuery()
      {
    	//prepare the query
        @$this->stmt = $this->conn->prepare($this->query);

		
    	//if prepare did not work properly
    	if($this->stmt==false)
    	  {
		    //generate time stamp
    	    $time = timeStamp();
    		
			//if there is any error in the preparing statements
			if(isset($this->conn->error))
			  {
    	        //prepare the error message
    	        $errorMessage = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), $this->conn->errno, $this->conn->error);
				
				$this->error = '<b>Prepare: </b> '.$this->conn->errno.' - '. $this->conn->error;
			  }
			else//if the error is in the query it self
			  {
			    $errorMessage = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), "N/A", "Error in MySQL Query");
				
				$this->error = '<b>Prepare: </b>Error in MySQL Query';
			  }
    		
    		//write error onto log file
    	    insertLog($errorMessage);

			return false;
    	  }//end of if
		  
		  return true;
      }/* End of prepareQuery */









    /*********************************************************************
     * bindQuery - this function is used to bind parameters to the query
    
     * Returns: true or false
    
     * Arguments: none
    
     * Method: we use the mysqli prepare function to prepare things and return the object
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    private function bindQuery()
      {
	    //check if the bind values array is a double dimensional array
		if(is_array($this->bindVals[0]))
		  {
	        //variable for making string of the parameters type
    	    $paramsString = "";

    	    //appending each parameter type to the variable
    	    foreach($this->bindVals as $key => $params)
    	      {
		        foreach($params as $param => $paramType)
		    	  {
				    if(is_string($paramType))//check if parameter type is a string
    	              $paramsString = $paramsString.$paramType;
					else//report error if parameter type is not a string
					  {
					    //load error into object
					    $this->error = "<b>Bind: </b> Parameter types supplied must be only strings";
						
						//generate time stamp
						$time = timeStamp();
    	    	
    	                //prepare the error message
    	                $errorMessage = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), "", $this->error);
            
    	                //write error onto log file
    	                insertLog($errorMessage);
						
						return false;
					  }
		    	  }
    	      }
    	      
    	      
    	    //inserting the params type into an array as first element
    	    $bindParams[] = &$paramsString;  
    	     
            
    	     
    	      
    	    //count bind values
    	    $count = count($this->bindVals);
			
			//array to hold the actual bind values
    	    $bindValsKeys = array();
			
    	    //appending each parameter value to the bind values
    	    for($i=0;$i<$count;$i++)
    	      {
			    //get the key of individual array
		        $temp = array_keys($this->bindVals[$i]);
				
				//populate the array with the bind values
		    	$bindValsKeys[] = $temp[0];
				
				//populate bind params with the addresses of bind values
    	    	$bindParams[] = &$bindValsKeys[$i];
    	      }
            
    	    //binding the parameters
    	    @$result = call_user_func_array(array($this->stmt, "bind_param"), $bindParams);
    	    
			//unset temporary variables
    	    unset($bindParams, $this->bindVals, $bindValsKeys);
            
    	    
    	    //if binding is not done properly
    	    if($result==false)
    	      {
			    //generate time stamp
    	        $time = timeStamp();
    	    	
    	        //prepare the error message
    	        $errorMessage = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), $this->conn->errno, "Could not bind values to the query");
            
    	    	//write error onto log file
    	        insertLog($errorMessage);
            
			    //load error into object
    	    	$this->error = '<b>Bind: </b> Could not bind values to the query';
		    	
		    	return false;
    	      }//end of if
		  
		  }
		else
		  {
		    //load error into object
		    $this->error = "<b>Bind: </b> Bind array supplied must be a two dimensional associative array";
			
			//generate time stamp
			$time = timeStamp();
    	    	
    	    //prepare the error message
    	    $errorMessage = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), "", $this->error);
            
    	    //write error onto log file
    	    insertLog($errorMessage);
			
		    return false;
		  }
		  
		return true;
      }/* End of bindQuery */









    /*********************************************************************
     * executeQuery - this function is used to execute the query
    
     * Returns: true - if the insertion was successful
                false - if there was an error in insertion
    
     * Arguments: 
    
     * Method: we use the mysqli prepare function to prepare things execute the update query
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    private function executeQuery()
      {
        ////////////////////////////////////////////////////////////////////
    	//if there is any problem in query preparation
		if($this->prepareQuery()==false)
    	  {
		    return false;
		  }//end of if
        ////////////////////////////////////////////////////////////////////
    	
    	
    	
    	
    	
    	
    	
        ////////////////////////////////////////////////////////////////////
    	if($this->bindVals!="")
    	  {
    	    //if there is any problem in binding the values with query
    	    if($this->bindQuery()==false)
    	      {
    	    	return false;
    	      }
    	  }//end of if
        ////////////////////////////////////////////////////////////////////


		

    	//execute the query
    	if($this->stmt->execute()==false)
    	  {
		    //generate time stamp
		    $time = timeStamp();
    		
    	    //prepare the error message
    	    $errorMessage = sprintf("[%-s %' 15s %' 30s] %' -6s $$$%'.-s$$$\n\n", $time, $_SERVER['REMOTE_ADDR'], date_default_timezone_get(), $this->conn->errno, $this->conn->error);
    
    		//write error onto log file
    	    insertLog($errorMessage);
			
			//load error into object
    	    $this->error = '<b>Execute: </b> '.$this->conn->errno.' - '. $this->conn->error;
			return false;
    	  }//end of if

		return true;
      }/* end of select */









    /*********************************************************************
     * fetchAll - this function is used to fetch all the resultant rows of a mysqli query
    
     * Returns: 2 dimensional array of all the resulting rows in the database
    
     * Arguments: query - query to be prepared
                  bindVals - array of values and their types to be bonded with the query
    			  rowCount - returns the number of rows of result, default: false
    			             if true, it only returns count ignoring results
    			  
    
     * Method: we use the mysqli prepare function to prepare things and return the object
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    public function fetchAll($query, $bindVals = "", $rowCount = false)
      {
	    //reset object parameters
	    $this->reset();
		
		//load the query
	    $this->query = $query;
		
		//load bind values
		$this->bindVals = $bindVals;
		

    	//if there are any problems in executing the query
    	if($this->executeQuery())
    	  {
    	    //if only number of rows are required
    	    if($rowCount)
    	      {
    	        //store the result 
    	        $this->stmt->store_result();
    	    
    	        //count number of rows
    	        $this->numRows = $this->stmt->num_rows;
				
				$retVal =  $this->numRows;
    	      }//end of if
    	    else
    	      {
    	        //get the result of mysqli query
                $rs = $this->stmt->get_result();
    	    	
    	    	//fetch all the rows of the result
    	    	$this->result = $rs->fetch_all(MYSQLI_ASSOC);
				
                //number of rows in the result
				$count = count($this->result);
				
				//if resultant has only one record, return only that record instead of double dimensional array
				if($count>1)
				  $retVal = $this->result;
				else if($count==1)
				  $retVal = $this->result[0];
                else
				  $retVal = "";
				  
				
    	      }//end of else
			  
			return $retVal;
    	  }//end of if
		else
		  {
		    return false;
		  }
    
      }/* end of select */









    /*********************************************************************
     * select - this function is used to execute the mysqli query to fetch things and returns an
                    intermediate object
    
     * Returns: result object which can be used for further fetching
    
     * Arguments: query - query to be prepared
                  bindVals - array of values and their types to be bonded with the query
    			  
    
     * Method: we use the mysqli prepare function to prepare things and return the object
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    public function select($query, $bindVals="")
      {
	    //reset temporary variables
	    $this->reset();
		
		//load mysqli query
	    $this->query = $query;
		
		//load bind values
		$this->bindVals = $bindVals;
		
		//if query is successfully executed
        if($this->executeQuery())
		  {
		    //load the result variable
		    $this->getResult = $this->stmt->get_result();
			
			//return result variable for further usage
			return $this->getResult;
		  }
		return false;
      }/* end of selectEach */

	  
	  
	  
	  
	  
	  
	  
	  
    /*********************************************************************
     * fetch - this function is used to fetch a row of mysqli query result as an array
    
     * Returns: associative array of fields and its values
    
     * Arguments: none
    
     * Method: we use the mysqli fetch function to fetch the things and return it
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    function fetch($result)
      {
    	//fetch and return the results as associated array
    	return $result->fetch_assoc();
      }/* End of fetch */

	  
	  
	  
	  
	  
	  
	  
	  
    /*********************************************************************
     * insert - this function is used to insert a row into the database
    
     * Returns: true - if the insertion was successful
                false - if there was an error in insertion
    
     * Arguments: query - query to be prepared
                  bindVals - array of values and their types to be bonded with the query
    
     * Method: we use the mysqli prepare function to prepare things execute the insert query
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    function insert($query, $bindVals="")
      {
	    //reset temporary variables
	    $this->reset();
		
		//load mysqli query
	    $this->query = $query;
		
		//load bind values
		$this->bindVals = $bindVals;

		//execute and return the result
		return $this->executeQuery();
      }/* end of select */









    /*********************************************************************
     * update - this function is used to update rows in the database
    
     * Returns: number of affected rows
    
     * Arguments: query - query to be prepared
                  bindVals - array of values and their types to be bonded with the query
    
     * Method: we use the mysqli prepare function to prepare things execute the update query
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    function update($query, $bindVals, $affectedRows = false)
      {
	    //reset temporary variables
	    $this->reset();
		
		//load mysqli query
	    $this->query = $query;
		
		//load bind values
		$this->bindVals = $bindVals;
		
        //executing the query
        if($this->executeQuery())
		  {
		    //check if affected rows are required
    	    if($affectedRows)
    	      {
			    //return affected rows
    	    	return $this->affectedRows = $this->stmt->affected_rows;
    	      }//end of if
			  
			return true;
		  }//end of if

		return false;
      }/* end of select */









    /*********************************************************************
     * delete - this function is used to delete selected rows in the database
    
     * Returns: true - if the insertion was successful
                false - if there was an error in insertion
    
     * Arguments: query - query to be prepared
                  bindVals - array of values and their types to be bonded with the query
    
     * Method: we use the mysqli prepare function to prepare things execute the update query
    
     * Bugs: None
    
     * To be done: nothing
    *********************************************************************/
    function delete($query, $bindVals, $affectedRows = false)
      {
	    //execute the update query as both of them are same
        return $this->update($query, $bindVals, $affectedRows);
      }/* end of select */
    /////////////MEMBER FUNCTIONS////////////////


    function runSimpleQuery($sql)
      {
        if($this->conn->query($sql)==false)
        {
            $this->error="Database Error :".$this->conn->error;
            return false;
        }
        else
        {
            return true;
        }

      }

    function startTransaction()
    {
        $this->conn->autocommit(false);
    }

    function completeTransaction()
    {
        $this->conn->commit();
        $this->conn->autocommit(true);
    }

    function rollbackTransaction()
    {
        $this->conn->rollback();
        $this->conn->autocommit(true);
    }

    //returns number of affected rows in case of insert or update
    function getAffectedRows()
    {
        return $this->stmt->affected_rows;
    }

    //Returns the value of Auto incremented Id generated due to last executed statement( i.e. if last statement was insert or update).
    //Returns id if there is a auto_incrementing field. Else return zero.
    function getInsertId()
    {
        return $this->conn->insert_id;
    }

    //Returns Fetched Row count (if the last function called is fetchAll or select)
    function getFetchedRowCount()
    {
        return $this->stmt->num_rows;
    }

    //Some Problem. Needs to be checked
    function setMySQLiRealConnect()
    {
        $con=mysqli_init();
        $this->conn=$con->real_connect(HOST,USER,PASSWORD,DB,MYSQLI::MYSQLI_CLIENT_FOUND_ROWS);
    }

    function getMatchedRowsOnUpdate()
    {
        preg_match_all('!\d+!', $this->conn->info, $m);
        return $m[0][0];
    }


  }/* End of QoB */




?>
