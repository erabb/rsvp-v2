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
		public function createPlusOne($data){
			
			$data = json_decode($data, true);
			
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

				$response = array("response"=>"true");

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

			//group of guests data
			//loop through guests ids and update
			//loop through


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
		public function getGuests($partyid){

		}

		//


	}

?>