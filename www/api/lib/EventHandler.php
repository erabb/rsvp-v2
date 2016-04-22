<?php

	class Event{

		public function Event(){

			global $db;
			$this->db = $db;
		
		}

		//crate events
		public function createEvent($data){

			$data = json_decode($data, true);

			$data['created'] = date('Y-m-d H:i:s');

			$result = $this->db->event()->insert($data);

			if($result){

				$response = array(
					
					'event_id' => $result['id'],
					'response' => 'true'
				
				);

			}else{

				$respose = array(

					'response' => 'false',
					'error' => 'Event not created.'

				);

			}

			return json_encode($response);

		}

		//get event id by user
		public function getEvent($userid){

			$events = $this->db->event('user_id = ?', $userid);

			return json_encode($events);

		}

	}

?>