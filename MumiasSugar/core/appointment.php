<?php

	class appointment
	{ 
		public $appointmentid = null;
		public $booked = null;
		public $weight  = null;
		public $address  = null;
		public $farmerid = null;
		public $transporterid = null;
		public $created = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{
			if ( isset( $data['appointmentid'] ) ) $this->appointmentid = (int) $data['appointmentid'];
			if ( isset( $data['booked'] ) ) $this->booked =  $data['booked'];
			if ( isset( $data['weight'] ) ) $this->weight =  $data['weight'];
			if ( isset( $data['address'] ) ) $this->address =  $data['address'];
			if ( isset( $data['farmerid'] ) ) $this->farmerid = $data['farmerid'];
			if ( isset( $data['transporterid'] ) ) $this->transporterid = $data['transporterid'];
			if ( isset( $data['created'] ) ) $this->created = $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = $data['updated'];
		}

		public function storeFormValues ( $params ) 
		{
			$this->__construct( $params );

			if ( isset($params['created']) ) {
				$created = explode ( '-', $params['created'] );

				if ( count($created) == 3 ) {
					list ( $y, $m, $d ) = $created;
					$this->created = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		public static function getById( $appointmentid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM appointments WHERE appointmentid = :appointmentid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":appointmentid", $appointmentid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new appointment( $row );
		}

		public static function getList() 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = 'SELECT appointmentid, booked, weight, address,
			CONCAT(farmers.firstname, " ", farmers.lastname) AS farmerid, 
			CONCAT(transporters.title, " - ", transporters.price) AS transporterid  
			FROM appointments 
			INNER JOIN farmers ON farmers.farmerid = appointments.farmerid 
			INNER JOIN transporters ON transporters.transporterid = appointments.transporterid 
			ORDER BY appointmentid DESC';

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$appointment = new appointment( $row );
				$list[] = $appointment;
			}

			$conn = null;
			return $list;
		}

		public static function getCancelled() 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = 'SELECT appointmentid, booked, weight, address,
			CONCAT(farmers.firstname, " ", farmers.lastname) AS farmerid
			FROM appointments 
			INNER JOIN farmers ON farmers.farmerid = appointments.farmerid 
			WHERE transporterid=0 ORDER BY appointmentid DESC';

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$appointment = new appointment( $row );
				$list[] = $appointment;
			}

			$conn = null;
			return $list;
		}

		public static function searchThis( $search ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM appointments WHERE booked LIKE '%".$search."%' ORDER BY created DESC";

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$appointment = new appointment( $row );
				$list[] = $appointment;
			}

			$conn = null;
			return $list;
		}

		public function insert() 
		{
			if ( !is_null( $this->appointmentid ) ) trigger_error ( "appointment::insert(): Attempt to insert an appointment object that already has its ID property set (to $this->appointmentid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO appointments ( booked, weight, address, farmerid, transporterid, created ) VALUES ( :booked, :weight, :address, :farmerid, :transporterid, :created)";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":booked", $this->booked, PDO::PARAM_STR );
			$st->bindValue( ":weight", $this->weight, PDO::PARAM_STR );
			$st->bindValue( ":address", $this->address, PDO::PARAM_STR );
			$st->bindValue( ":farmerid", $this->farmerid, PDO::PARAM_STR );
			$st->bindValue( ":transporterid", $this->transporterid, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->appointmentid = $conn->lastInsertId();
			$conn = null;
			return $this->appointmentid;
		}
		
		public function update() 
		{
			if ( is_null( $this->appointmentid ) ) trigger_error ( "appointment::update(): Attempt to update an appointment object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE appointments SET booked=:booked, weight=:weight, address=:address, farmerid=:farmerid, transporterid=:transporterid, updated=:updated WHERE appointmentid = :appointmentid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":booked", $this->booked, PDO::PARAM_STR );
			$st->bindValue( ":weight", $this->weight, PDO::PARAM_STR );
			$st->bindValue( ":address", $this->address, PDO::PARAM_STR );
			$st->bindValue( ":farmerid", $this->farmerid, PDO::PARAM_STR );
			$st->bindValue( ":transporterid", $this->transporterid, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":appointmentid", $this->appointmentid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function cancel() 
		{
			if ( is_null( $this->appointmentid ) ) trigger_error ( "appointment::cancel(): Attempt to update an appointment object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE appointments SET transporterid=:transporterid, updated=:updated WHERE appointmentid = :appointmentid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":transporterid", 0, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":appointmentid", $this->appointmentid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{

			if ( is_null( $this->appointmentid ) ) trigger_error ( "appointment::delete(): Attempt to delete an appointment object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM appointments WHERE appointmentid = :appointmentid LIMIT 1" );
			$st->bindValue( ":appointmentid", $this->appointmentid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
