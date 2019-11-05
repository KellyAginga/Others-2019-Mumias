<?php
	function tableCreate( $table,  $variables = array() ) 
	{
		try {
			$fields = array();
			$values = array();
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE IF NOT EXISTS ". $table;
			foreach( $variables as $field ) $fields[] = $field;
			$fields = ' (' . implode(', ', $fields) . ')';      
			$sql .= $fields;
			$conn->exec( $sql );
		} catch(PDOException $exception) {
			$as_err['errno'] = 3;
			$as_err['errtitle'] = 'Database action failed';
			$as_err['errsumm'] = 'Creating the table '. $table . ' failed';
			$as_err['errfull'] = $exception->getMessage();
		}
		$conn = null;
	}
	
	function createTables()
	{
		tableCreate( 'transporters',  
			array(//name, mobile, email, address, rate, created, updated
				'transporterid int(11) NOT NULL AUTO_INCREMENT',
				'name varchar(100) NOT NULL',
				'mobile int(11) DEFAULT 0',
				'email varchar(100) NOT NULL',
				'address varchar(100) NOT NULL',
				'rate varchar(100) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (transporterid)',
				'UNIQUE row1(name)',
				'UNIQUE row2(mobile)',
				'UNIQUE row3(email)',
			)
		);
		
		tableCreate( 'appointments',  
			array(//booked, weight, address, farmerid, transporterid, created, updated
				'appointmentid int(11) NOT NULL AUTO_INCREMENT',
				'booked varchar(50) NOT NULL',
				'weight varchar(100) NOT NULL',
				'address varchar(100) NOT NULL',
				'farmerid int(11) DEFAULT 0',
				'transporterid int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (appointmentid)',
			)
		); 
		
		tableCreate( 'options',
			array(
				'optionid int(11) NOT NULL AUTO_INCREMENT',
				'title varchar(100) NOT NULL',
				'content varchar(2000) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (optionid)',
			)
		); 
		
		tableCreate( 'farmers',  
			array(//firstname, lastname, handle, email, mobile, address, sex, password, created, updated
				'farmerid int(11) NOT NULL AUTO_INCREMENT',
				'firstname varchar(50) NOT NULL',
				'lastname varchar(50) NOT NULL',
				'handle varchar(100) NOT NULL',
				'email varchar(100) NOT NULL',
				'mobile int(11) DEFAULT 0',
				'address varchar(100) NOT NULL',
				'sex int(10) NOT NULL DEFAULT 1',
				'password int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (farmerid)',
				'UNIQUE email_address(email)',
			)
		);
		
		tableCreate( 'managers', 
			array(
				'managerid int(11) NOT NULL AUTO_INCREMENT',
				'handle varchar(50) NOT NULL',
				'firstname varchar(50) NOT NULL',
				'lastname varchar(50) NOT NULL',
				'mobile varchar(50) NOT NULL',
				'idnumber varchar(50) NOT NULL',
				'sex int(10) NOT NULL DEFAULT 1',
				'password text NOT NULL',
				'email varchar(200) NOT NULL',
				'level int(10) NOT NULL DEFAULT 0',
				'joined datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (managerid)',
			)
		);
		
	}
	createTables();
	
	function checkTables( $table ) 
	{
		$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
		$sql = "SELECT * FROM " . $table . " LIMIT 1";
		$st = $conn->prepare( $sql );
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if ( $row ) return 0;
		else return 1;
	}