<?php 
	class User {
		private $id;
		private $name;
		private $email;
		private $pass;
		private $phone;
		private $isActive;
		private $isAdmin;
		private $updatedBy;
		private $updatedOn;
		private $createdBy;
		private $createdOn;
		private $tableName = 'users';
		private $dbConn;

		function setId($id) { $this->id = $id; }
		function getId() { return $this->id; }
		function setName($name) { $this->name = $name; }
		function getName() { return $this->name; }
		function setEmail($email) { $this->email = $email; }
		function getEmail() { return $this->email; }
		function setPass($pass) { $this->pass = $pass; }
		function getPass() { return $this->pass; }
		function setPhone($phone) { $this->phone = $phone; }
		function getPhone() { return $this->phone; }
		function setIsActive($isActive) { $this->isActive = $isActive; }
		function getIsActive() { return $this->isActive; }
		function setIsAdmin($isAdmin) { $this->isAdmin = $isAdmin; }
		function getIsAdmin() { return $this->isAdmin; }
		function setUpdatedBy($updatedBy) { $this->updatedBy = $updatedBy; }
		function getUpdatedBy() { return $this->updatedBy; }
		function setUpdatedOn($updatedOn) { $this->updatedOn = $updatedOn; }
		function getUpdatedOn() { return $this->updatedOn; }
		function setCreatedBy($createdBy) { $this->createdBy = $createdBy; }
		function getCreatedBy() { return $this->createdBy; }
		function setCreatedOn($createdOn) { $this->createdOn = $createdOn; }
		function getCreatedOn() { return $this->createdOn; }

		public function __construct() {
			$db = new DbConnect();
			$this->dbConn = $db->connect();
		}

		public function getAllUser() {
			$stmt = $this->dbConn->prepare("SELECT * FROM " . $this->tableName);
			$stmt->execute();
			$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $customers;
		}

		public function getUserDetailsById() {

			$sql = "SELECT 
						c.*, 
						u.user_name as created_user,
						u1.user_name as updated_user
					FROM users c 
						JOIN users u ON (c.created_by = u.user_id) 
						LEFT JOIN users u1 ON (c.updated_by = u1.user_id) 
					WHERE 
						c.user_id = :userId";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':userId', $this->id);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			return $user;
		}
		

		public function insert() {
			
			$sql = 'INSERT INTO ' . $this->tableName . 
			'(user_id, user_name, user_email, user_pass, user_phone, is_active, is_admin, created_by, created_on) 
			VALUES(null, :name, :email, :pass, :phone, :isActive, :isAdmin, :createdBy, :createdOn)';

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':name', $this->name);
			$stmt->bindParam(':email', $this->email);
			$stmt->bindParam(':pass', $this->pass);
			$stmt->bindParam(':phone', $this->phone);
			$stmt->bindParam(':isActive', $this->isActive);
			$stmt->bindParam(':isAdmin', $this->isAdmin);
			$stmt->bindParam(':createdBy', $this->createdBy);
			$stmt->bindParam(':createdOn', $this->createdOn);
			
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function update() {
			
			$sql = "UPDATE $this->tableName SET";
			if( null != $this->getName()) {
				$sql .=	" user_name = '" . $this->getName() . "',";
			}

			if( null != $this->getPass()) {
				$sql .=	" user_pass = '" . $this->getPass() . "',";
			}

			if( null != $this->getPhone()) {
				$sql .=	" user_phone = " . $this->getPhone() . ",";
			}

			$sql .=	" updated_by = :updatedBy, 
					  updated_on = :updatedOn
					WHERE 
						user_id = :userId";

			$stmt = $this->dbConn->prepare($sql);
			$stmt->bindParam(':userId', $this->id);
			$stmt->bindParam(':updatedBy', $this->updatedBy);
			$stmt->bindParam(':updatedOn', $this->updatedOn);
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}

		public function delete() {
			$stmt = $this->dbConn->prepare('DELETE FROM ' . $this->tableName . ' WHERE user_id = :userId');
			$stmt->bindParam(':userId', $this->id);
			
			if($stmt->execute()) {
				return true;
			} else {
				return false;
			}
		}
	}
 ?>