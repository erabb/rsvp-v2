<?php
	
	class User{

		public function User(){
			
			global $db;

			$this->db = $db;

		}

		//create user profile
		public function createUser($data){

			$data = json_decode($data, true);

			$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

			$data['created'] = date('Y-m-d H:i:s');

			$result = $this->db->user()->insert($data);

			if($result){
				
				$response = array( 'id'=>$result['id'], 'response' => 'true');
				
				return json_encode($response);
			
			}else{

				$error = array('response' => 'false', 'error' => 'Registration failed');
				
				return json_encode($error);

			}

		}

		//user login
		public function login($data){

			$data = json_decode($data, TRUE);

			$user = $this->db->user( 'email = ?', $data['email'])->fetch(); 

			$result = password_verify( $data['password'], $user['password']);

			if($result){
				
				$expTime = 60*60*4;

				//set cookie token
				$payload = array(
					'iat' => time(),
					'exp' => time() + $expTime,//
					'userid' => $user['id'],
					'response' => 'true',
					'authenticate' => 'true'
				);

				$token = JWT::encode($payload, 'rsvp99secret');

				global $app;
				$app->setcookie('token', $token, time() + $expTime);

				$response = array('response'=>'true');

				return json_encode($response);

			}else{

				$error = array('error' => 'Either the email or password is incorrect.');

				return json_encode($error);

			}	

		}

		//user logout
		public function logout(){
			
			//reset cookie and expire token with new expired token
			$app = \Slim\Slim::getInstance();
			
			$app->deleteCookie('token');

			$response = array(
				
				"response"=>"true"
			
			);

			return json_encode($response);			

		}

		//user profile edit, by admin or user
		public function editUser($admin = false, $id = 0){

			$response = array(
				
				"response"=>"true"
			
			);

			return json_encode($response);

		}

		//get user profie
		public function getUser($id){

			$user = $this->db->user('id = ?', $id)->fetch();

			return json_encode($user);

		}

		//password change
		public function passwordChange($data, $id = 1){

			$data = json_decode($data, true);

			$app = \Slim\Slim::getInstance();
			
			$userid = $app->userid;

			if(empty($userid)){

				$userid = $id;

			}

			$user = $this->db->user('id = ?', $id)->fetch();

			if( password_verify( $data['password'], $user['password']) ){

				$update = array(
					'password' => password_hash( $data['password_new'], PASSWORD_DEFAULT ),
					'modified' => date('Y-m-d H:i:s')
				);

				$this->db->user()->update($update);

				$response = array('response' => 'true');

			}else{

				$response = array('response' => 'Unable to change password');

			}

			return json_encode($response);

		}

		//convert phone number to pretty format
		private function convertPhone($number){
			
			$cleanNumber = preg_replace("/[^0-9]/","",$number);

			$phone = "(".substr($cleanNumber, 0, 3).") ".substr($cleanNumber, 3, 3)."-".substr($cleanNumber,6);
		
			return $phone;
		}

	}
?>	