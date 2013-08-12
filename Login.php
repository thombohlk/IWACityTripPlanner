<?php

function cleanPost($val) {
  if(!isset($_POST[$val])) {
    $_POST[$val] = NULL;
    return;
  }
  $_POST[$val] = trim(htmlentities($_POST[$val], ENT_QUOTES, 'UTF-8'));
}

function validateUser($myusername, $mypassword) {
	$host="localhost"; // Host name 
	$username="www"; // Mysql username 
	$password="d@m1je!"; // Mysql password 
	$db_name="city_trip_planner"; // Database name

	// Connect to server and select databse.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name"); 

	// To protect MySQL injection (more detail about MySQL injection)
	$myusername = stripslashes($myusername);
	$mypassword = stripslashes($mypassword);
	$myusername = $mysqli->real_escape_string($myusername);
	$mypassword = $mysqli->real_escape_string($mypassword);
	$sql="SELECT * FROM members WHERE username='$myusername' AND password='$mypassword';";
	$result = $mysqli->query($sql);
	$mysqli->close();
	
	// Mysql_num_row is counting table row
	$count = $result->num_rows;

	// If result matched $myusername and $mypassword, table row must be 1 row
	if($count==1){
		$user = $result->fetch_row();
		// Register $myusername, $mypassword and redirect to file "login_success.php"
		session_start(); 
		$_SESSION["userid"] = $user[0];
		$_SESSION["username"] = $user[1];
		return true;
	} else {
		return false;
	}
}

function getCityTrip($userid) {
	$host="localhost"; // Host name 
	$username="www"; // Mysql username 
	$password="d@m1je!"; // Mysql password 
	$db_name="city_trip_planner"; // Database name

	// Connect to server and select databse.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name"); 

	// To protect MySQL injection (more detail about MySQL injection)
	$userid = stripslashes($userid);
	$userid = $mysqli->real_escape_string($userid);
	$sql="SELECT xml FROM city_trips WHERE userid='$userid';";
	$results = $mysqli->query($sql);
	$mysqli->close();
	
	// Mysql_num_row is counting table row
	$count = $results->num_rows;

	// If result matched $myusername and $mypassword, table row must be 1 row
	if($count==1){
		$result = $results->fetch_row();
		return json_decode($result[0]);
	} else {
		return false;
	}
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	cleanPost('user');
	cleanPost('pass');

	if(validateUser($_POST['user'], $_POST['pass'])) {
		$userPrefs = array(
			'name' => $_SESSION["username"],
			'cityTrip' => getCityTrip($_SESSION['userid'])
		);
		echo json_encode($userPrefs);
	} else {
		$userPrefs = array(
			'name' => $_POST['user'],
			'pass' => $_POST['pass']
		);
		echo json_encode($userPrefs);
		header('HTTP/1.1 403 Forbidden');
		echo 'Invalid login information provided';
	}
} else {
	header('HTTP/1.1 404 Not Found');
	echo '404 page not found!'; // well you will have to make it prettier!
}
?>
