<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'vendor/autoload.php';

//NOT ORM config
$pdo = new PDO("mysql:host=localhost;dbname=rsvp", 'root', 'root');
$db = new NotOrm($pdo);

//instaantiate Slim
$app = new \Slim\Slim();

$user = new User();
$event = new Event();
$guest = new Guest();


$app->get('/hello/:name', function ($name){
    echo "Hello, " . $name;
});


/********************************** USER **********************************/

//create user
$app->post('/user', function() use($user, $app) {

	$data = $app->request()->getBody();

	echo $user->createUser($data);

});

// //get user
$app->get('/user/:id', function ($id) use($user){
    
	echo $user->getUser($id);

});

//edit user
$app->put('/user/:id', function ($id) use( $user ){

	$data = $app->request()->getBody();

	echo $user->editUser($data);

});

//login user
$app->post('/login', function () use( $user, $app ){

	$data = $app->request()->getBody();

	echo $user->login($data);

});

//logout user
$app->get('/logout', function() use( $user ){

	echo $user->logout();

});

//password reset
$app->post('/password-change', function() use($user, $app){

	$data = $app->request()->getBody();

	echo $user->passwordChange($data);

});


/********************************** GUEST **********************************/

//add guests to event
$app->post('/guest/:eventid', function($event_id) use($guest, $app) {

	$data = $app->request()->getBody();

	echo $guest->createGuest($event_id, $data);

});

$app->post('/guest-delete/:guestid', function($guest_id) use($guest){

	echo $guest->deleteGuest($guest_id);

});

$app->post('/guest-plus-one', function() use($guest, $app){

	$data = $app->request()->getBody();

	echo $guest->createPlusOne($data);

});

$app->put('/guest-edit/:id', function($id) use( $guest, $app ){

	$data = $app->request()->getBody();

	echo $guest->editGuest( $id, $data );

});

$app->put('/guest-rsvp', function() use ($guest, $app){

	$data = $app->request()->getBody();

	echo $guest->rsvp($data);

});

$app->get('/guest/:eventid', function($eventid) use ($guest, $app){

	echo $guest->getGuests($eventid);

});

$app->get('/guest-party/:eventid/:partyid', function($eventid,$partyid) use ($guest){

	echo $guest->getGuestByParty($eventid, $partyid);

});

$app->get('/guest-code/:eventid/:code', function($eventid, $code) use ($guest){

	echo $guest->guestCodeLogin($eventid, $code);

});

	
/********************************** EVENT **********************************/

//create event
$app->post('/event', function() use($event, $app) {

	$data = $app->request()->getBody();

	echo $event->createEvent($data);

});


/********************************** DASHBOARD **********************************/

$app->run();


?>