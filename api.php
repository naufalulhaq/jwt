<?php
    class Api extends Rest {
        public $dbConn;

        public function __construct() {
            parent::__construct();
        }

        public function generateToken() {
            $email = $this->validateParameter(
                'email', $this->param['email'], STRING);
            $pass = $this->validateParameter(
                'pass', $this->param['pass'], STRING);

            try {
                $stmt = $this->dbConn->
                    prepare("SELECT * FROM users WHERE user_email = :email AND user_pass = :pass");
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":pass", $pass);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if(!is_array($user)) {
                    $this->returnResponse(INVALID_USER_PASS, 
                    "Email or Password is incorrect.");
                }
                if($user['is_active']==0) {
                    $this->returnResponse(USER_NOT_ACTIVE,
                    "User is not activated. Please contact to admin.");
                }

                $payload = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (15*60),
                    'userId' => $user['user_id'],
                    'isAdmin'=> $user['is_admin']
                ];

                $token = JWT::encode($payload, SECRET_KEY);
                
                $data = ['token' => $token];
                $this->returnResponse(SUCCESS_RESPONSE, $data);
            } catch (Exception $e) {
                $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
            }
        }

        public function addUser() {
            $name = $this->validateParameter(
                'name', $this->param['name'], STRING);
            $email = $this->validateParameter(
                'email', $this->param['email'], STRING);
            $pass = $this->validateParameter(
                'pass', $this->param['pass'], STRING);
            $phone = $this->validateParameter(
                'phone', $this->param['phone'], INTEGER, false);
            $isActive = $this->validateParameter(
                'isActive', $this->param['isActive'], BOOLEAN, false);
            $isAdmin = $this->validateParameter(
                'isAdmin', $this->param['isAdmin'], BOOLEAN, false);

            $newUser = new User;
            $newUser->setName($name);
            $newUser->setEmail($email);
            $newUser->setPass($pass);
            $newUser->setPhone($phone);
            $newUser->setIsActive($isActive);
            $newUser->setIsAdmin($isAdmin);
            $newUser->setCreatedBy($this->userId);
            $newUser->setCreatedOn(date('y-m-d'));

            if ($this->isAdmin == 1) {
                if (!$newUser->insert()) {
                    $message = 'Failed to insert.';
                } else {
                    $message = 'Inserted successfully.';
                }
            } else {
                $message = 'This user has no authority over this service. Only Admin can add new user.';
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function getUserDetailsById() {
            $userId = $this->validateParameter(
                'userId', $this->param['userId'], INTEGER);

            $newUser = new User;
            $newUser->setId($userId);

            if ($this->isAdmin == 0 && $this->userId != $userId) {
                $message = 'This user has no authority over this service. Only Admin can get other user details.';
            } else {
                $user = $newUser->getUserDetailsById();

                if (!is_array($user)) {
                    //$this->returnResponse(SUCCESS_RESPONSE, 
                    //['message' => 'User details are not found']);
                    $message = 'User details are not found.';
                }

                $response['userId']         = $user['user_id'];
                $response['Name']           = $user['user_name'];
                $response['email']          = $user['user_email'];
                $response['pass']           = $user['user_pass'];
                $response['phone']          = $user['user_phone'];
                $response['isActive']       = $user['is_active'];
                $response['isAdmin']        = $user['is_admin'];
                $response['createdBy']      = $user['created_user'];
                $response['lasstUpdatedBy'] = $user['updated_user'];
                
                $message = $response;
                //$this->returnResponse(SUCCESS_RESPONSE, $response);
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function getAllUserDetails() {
            $newUser = new User;
            if ($this->isAdmin == 0) {
                $message = 'This user has no authority over this service. Only Admin can get all user details.';
            } else {
                $user = $newUser->getAllUser();

                if (!is_array($user)) {
                    $message = 'User details are not found.';
                } else {
                    $message = $user;
                    //$this->returnResponse(SUCCESS_RESPONSE, $response);
                }

            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function updateUser() {
            $userId = $this->validateParameter(
                'userId', $this->param['userId'], INTEGER);
            $name = $this->validateParameter(
                'name', $this->param['name'], STRING);
            $pass = $this->validateParameter(
                'pass', $this->param['pass'], STRING, false);
            $phone = $this->validateParameter(
                'phone', $this->param['phone'], INTEGER, false);

            $newUser = new User;
            $newUser->setId($userId);
            $newUser->setName($name);
            $newUser->setPass($pass);
            $newUser->setPhone($phone);
            $newUser->setUpdatedBy($this->userId);
            $newUser->setUpdatedOn(date('y-m-d'));

            
            if ($this->isAdmin == 0 && $this->userId != $userId) {
                $message = 'This user has no authority over this service. Only Admin can update other user.';
            } else {
                if (!$newUser->update()) {
                    $message = 'Failed to update.';
                } else {
                    $message = 'Updated successfully.';
                }
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
        }

        public function deleteUser() {
            $userId = $this->validateParameter(
                'userId', $this->param['userId'], INTEGER);

            $newUser = new User;
            $newUser->setId($userId);

            if ($this->isAdmin == 1) {
                if (!$newUser->delete()) {
                    $message = 'Failed to delete.';
                } else {
                    $message = 'Deleted successfully.';
                }
            } else {
                $message = 'This user has no authority over this service. Only Admin can delete user.';
            }

            $this->returnResponse(SUCCESS_RESPONSE, $message);
            
        }
    }
?>