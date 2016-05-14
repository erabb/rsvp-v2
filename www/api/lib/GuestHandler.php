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
				'isPlusGuest' => 1
			
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

				$response = array("response"=>"true","guestplus_is"=>$guest_result['id']);

			} else{

				$response = array("response"=>"false","error"=>"Unable to add plus one guest.");

			}

			return json_encode($response);

		}	

		//user edits guest info
		public function editGuest($id, $data){

			$data = json_decode($data, true);

			$data['modified'] = date('Y-m-d H:i:s');

			$result = $this->db->guest[$id]->update($data);

			if($result){

				$response = array("response"=>"true");

			}else{

				$response = array("response"=>"false","error"=>"Unable to edit guest.");

			}

			return json_encode($response);

		}


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

		//if guests edits their own info
		public function editGuestsSelf($id, $data){

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

		//handle guest finding their name
		public function guestLogin($data){

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

					$guestPlus = $this->db->guest('id = ?', $plus['id']);

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