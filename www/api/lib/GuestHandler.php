<?php

	class Guest{

		public function Guest(){

			global $db;
			$this->db = $db;

		}

		// loop through add guest data and create guest and party
		public function createGuest($event_id, $data){

			$data = json_decode($data, true);

			$party_id = $this->createParty($event_id);	

			//add each guest to guests table
			foreach ($data['guests'] as $guest) {
				
				$guest['created'] = date('Y-m-d H:i:s');
				$guest['event_id'] = $event_id;
				$guest['party_id'] = $party_id;

				$this->db->guest()->insert($guest);

			}

			$response = array("response"=>"true");

			return json_encode($response);
		}

		//remove guest
		public function deleteGuest($guest_id){

			$update = array(
				"modified" => date('Y-m-d H:i:s'),
				"status" => "inactive"
			);

			$result = $this->db->guest[$guest_id]->update($update);

			if($result){

				$response = array("response"=>"true");

			}else{

				$response = array("response"=>"false","error"=>"Unable to delete guests.");

			}

			return json_encode($response);

		}

		//guest adds plus one guest
		public function createPlusOne($data, $questions){
			
			//$data = json_decode($data, true);
			
			//create guest
			$guest = array(

				'created' => date('Y-m-d H:i:s'),
				'event_id' => $data['event_id'],
				'party_id' => $data['party_id'],
				'first_name' =>$data['first_name'],
				'last_name' => $data['last_name'],
				'allowedGuest' => 0,
				'email' => $data['email'],
				'note' => $data['note'],
				'code' => $data['code'],
				'isPlusGuest' => 1,
				'isComing' => 1
			
			);

			$guest_result = $this->db->guest()->insert($guest);

			//use create guest get back the id 
			if($guest_result){

				$plus = array(
					'guest_id' => $data['guest_id'],
					'guestplus_id' => $guest_result['id']
				);

				//enter id into guest_plus record
				$this->db->guest_to_guestplus()->insert($plus);

				//then update question answers
				foreach ($questions as $q) {
					
					$a = array(
						'question_id'=>$q['question_id'],
						'guest_id' => $guest_result['id'],
						'answer'=>$q['answer']
					);

					$this->db->answer()->insert($a);

				}
				$guest['id'] = $guest_result['id'];

				//return guest so that front end can create full guest model with info
				$response = array("response"=>"true","guest"=>$guest);


			} else{

				$response = array("response"=>"false","error"=>"Unable to add plus one guest.");

			}

			return json_encode($response);

		}	

		//user edits guest info
		// public function userEditGuest($id, $data){

		// 	$data = json_decode($data, true);

		// 	$data['modified'] = date('Y-m-d H:i:s');

		// 	$result = $this->db->guest[$id]->update($data);

		// 	if($result){

		// 		$response = array("response"=>"true");

		// 	}else{

		// 		$response = array("response"=>"false","error"=>"Unable to edit guest.");

		// 	}

		// 	return json_encode($response);

		// }


		//guests rsvps or edits own status
		public function rsvp($data){

			$data = json_decode($data, true);

			//loop through party guests
			foreach ($data['guest'] as $guest) {
				$guest['info']['modified'] = date('Y-m-d H:i:s');

				//for each guests update info
				$this->db->guest[$guest['id']]->update($guest['info']);

				//then update question answers
				foreach ($guest['question'] as $q) {

					$a = array(
						'question_id'=>$q['question_id'],
						'guest_id' => $guest['id'],
						'answer'=>$q['answer']
					);

					$this->db->answer()->insert($a);

				}

				//if plus one send guestinfo
				if($guest['plus_one']){

					// enter plusone guest info
					$this->createPlusOne($guest['plus_one']['info'], $guest['plus_one']['question']);

				}

				$response = array("response"=>"true");
				return json_encode($response);
			}


		}

		//guests edit individual
		public function editGuest($id, $data){

			$guest = json_decode($data, true);

			$guest['info']['modified'] = date('Y-m-d H:i:s');

			$result = $this->db->guest[$guest['id']]->update($guest['info']);

			foreach ($guest['question'] as $q) {
				$answerResult = $this->db->answer('user_id = ?', $guest['id'])->and('question_id = ?', $q['id'])->fetch();

				$question = array(
					'answer' => $q['answer']
				);

				$this->db->answer[$answerResult['id']]->update($question);

			}

			$response = array('response'=>'true','guest'=>$guest);

		}

		//create a party id for guest groupijng
		private function createParty($event_id){

			//create a party for group of guests
			$createParty = array(
				'event_id'=>$event_id
			);

			$party = $this->db->party()->insert($createParty);

			return $party['id'];

		}

		public function guestCodeLogin($eventid, $code){
			
			$guest = $this->db->guest('event_id = ?', $eventid)->and('code = ?', $code)->fetch();

			if($guest){

				$response = $this->getGuestByParty($eventid, $guest['party_id']);
				echo $response;

			}else{

				$response = array(

					'response' => 'false',
					'error' => 'Guest code not found'
				);

				return json_encode($response);
			}

		}

		//both types of login call this function to get party info
		public function getGuestByParty($eventid, $partyid){
			$guestHolder = [];

			$guests = $this->db->guest('isPlusGuest = ?', 0)->and('event_id = ?', $eventid)->and('party_id = ?', $partyid);

			if(count($guests) < 1){

				$error = array('response' => 'false', 'error' => 'Guests not found');
				return json_encode($error);
			}

			foreach ($guests as $g) {
				$guest = [];
				$guest['id'] = $g['id'];
				$guest['info'] = $g;

				//pull questions
				$questions = $this->db->answer('guest_id = ?', $g['id']);
				$question = [];
				
				foreach ($questions as $q) {


					$qArray = array(
						
						'question_id'=>$q['question_id'],
						'answer'=>$q['answer']

					);

					array_push($question, $qArray);

				}

				$guest['question'] = $question;

				//check to see if plus guest exists
				$plus = $this->db->guest_to_guestplus('guest_id = ?', $g['id'])->fetch();

				//get plus one info
				if($plus){
					$plus_one = [];

					$guestPlus = $this->db->guest('id = ?', $plus['id'])->fetch();

					$plus_one['info'] = $guestPlus;

					//pull questions
					$questionsPlus = $this->db->answer('guest_id = ?', $plus['id']);
					$questionPlus = [];
					
					foreach ($questionsPlus as $qPlus) {


						$qArrayPlus = array(
							
							'question_id'=>$qPlus['question_id'],
							'answer'=>$qPlus['answer']

						);

						array_push($questionPlus, $qArrayPlus);

					}

					$plus_one['question'] = $questionPlus;

					$guest['plus_one'] = $plus_one;

					

				}

				array_push($guestHolder, $guest);
			}

			return json_encode($guestHolder);	
		}

		//get all of the guests lists for admin / guest lookup
		public function getGuests($eventid){
			$guestHolder = [];

			$guests = $this->db->guest('isPlusGuest = ?', 0)->where('event_id = ?', $eventid);

			foreach ($guests as $g) {
				$guest = [];
				$guest['id'] = $g['id'];
				$guest['info'] = $g;

				//pull questions
				$questions = $this->db->answer('guest_id = ?', $g['id']);
				$question = [];
				
				foreach ($questions as $q) {


					$qArray = array(
						
						'question_id'=>$q['question_id'],
						'answer'=>$q['answer']

					);

					array_push($question, $qArray);

				}

				$guest['question'] = $question;

				//check to see if plus guest exists
				$plus = $this->db->guest_to_guestplus('guest_id = ?', $g['id'])->fetch();

				//get plus one info
				if($plus){
					$plus_one = [];

					$guestPlus = $this->db->guest('id = ?', $plus['id'])->fetch();

					$plus_one['info'] = $guestPlus;

					//pull questions
					$questionsPlus = $this->db->answer('guest_id = ?', $plus['id']);
					$questionPlus = [];
					
					foreach ($questionsPlus as $qPlus) {


						$qArrayPlus = array(
							
							'question_id'=>$qPlus['question_id'],
							'answer'=>$qPlus['answer']

						);

						array_push($questionPlus, $qArrayPlus);

					}

					$plus_one['question'] = $questionPlus;

					$guest['plus_one'] = $plus_one;

					

				}

				array_push($guestHolder, $guest);
			}

			return json_encode($guestHolder);	

		}



	}

?>