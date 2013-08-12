<?php

function cleanPost($val) {
  if(!isset($_POST[$val])) {
    $_POST[$val] = NULL;
    return;
  }
  $_POST[$val] = trim(htmlentities($_POST[$val], ENT_QUOTES, 'UTF-8'));
}


function saveCityTrip($cityTripJSON) {
	if (userAlreadyHasCityTrip($_SESSION['userid'])) {
		return updateCityTrip($cityTripJSON);
	} else {
		return createCityTrip($cityTripJSON);
	}
}

function userAlreadyHasCityTrip($userid) {
	$host="localhost"; // Host name 
	$username="www"; // Mysql username 
	$password="d@m1je!"; // Mysql password 
	$db_name="city_trip_planner"; // Database name 

	// Connect to server and select databse.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name"); 

	// To protect MySQL injection (more detail about MySQL injection)
	$myusername = stripslashes($myusername);
	$myusername = $mysqli->real_escape_string($myusername);
	$sql = "SELECT * FROM city_trips WHERE userid = '$userid';";
	$result = $mysqli->query($sql);
	$mysqli->close();
	
	// Mysql_num_row is counting table row
	$count = $result->num_rows;

	// If result matched $myusername, table row must be 1 row
	if ($count==1) {
		echo "user trip found";
		return true;
	} else {
		echo "no user trip found";
		return false;
	}
}

function updateCityTrip($cityTripJSON) {
	$cityTripText = json_encode($cityTripJSON);
	$userid = $_SESSION['userid'];
	
	$host="localhost"; // Host name 
	$username="www"; // Mysql username 
	$password="d@m1je!"; // Mysql password 
	$db_name="city_trip_planner"; // Database name

	// Connect to server and select databse.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name"); 

	// To protect MySQL injection (more detail about MySQL injection)
	$cityTripText = stripslashes($cityTripText);
	$userid = stripslashes($userid);
	$cityTripText = $mysqli->real_escape_string($cityTripText);
	$userid = $mysqli->real_escape_string($userid);
	$sql="UPDATE city_trips SET xml = '$cityTripText' WHERE userid = '$userid';";
	$result = $mysqli->query($sql);
	$mysqli->close();

	return $result;
}

function createCityTrip($cityTripJSON) {
	$cityTripText = json_encode($cityTripJSON);
	$userid = $_SESSION['userid'];
	
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
	$sql = "INSERT INTO city_trips (userid, xml) VALUES ('$userid', '$cityTripText');";
	$result = $mysqli->query($sql);
	$mysqli->close();

	return $result;
}

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	session_start();
	if(isset($_SESSION['userid'])) {
		echo "wazaaa: ";
		if (saveCityTrip($_POST['cityTripJSON'])) {
			echo "Saved!";
		} else {
			echo "Failed!";
		}
	} else {
		header('HTTP/1.1 403 Forbidden');
		echo 'You are not logged in yet.';
	}
} else {
	header('HTTP/1.1 404 Not Found');
	echo '404 page not found!'; // well you will have to make it prettier!
}
?>
