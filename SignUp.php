<?php

function cleanPost($val) {
  if(!isset($_POST[$val])) {
    $_POST[$val] = NULL;
    return;
  }
  $_POST[$val] = trim(htmlentities($_POST[$val], ENT_QUOTES, 'UTF-8'));
}

function userAlreadyExists($myusername) {
	$host="localhost"; // Host name 
	$username="www"; // Mysql username 
	$password="d@m1je!"; // Mysql password 
	$db_name="city_trip_planner"; // Database name 
	$tbl_name="members"; // Table name 

	// Connect to server and select databse.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name"); 

	// To protect MySQL injection (more detail about MySQL injection)
	$myusername = stripslashes($myusername);
	$myusername = $mysqli->real_escape_string($myusername);
	$sql = "SELECT * FROM $tbl_name WHERE username = '$myusername';";
	$result = $mysqli->query($sql);
	$mysqli->close();
	
	// Mysql_num_row is counting table row
	$count = $result->num_rows;

	// If result matched $myusername, table row must be 1 row
	if($count==1){
		return true;
	} else {
		return false;
	}
}
function registerUser($myusername, $mypassword) {
	$host="localhost"; // Host name 
	$username="www"; // Mysql username 
	$password="d@m1je!"; // Mysql password 
	$db_name="city_trip_planner"; // Database name 
	$tbl_name="members"; // Table name 

	// Connect to server and select databse.
	$mysqli = new mysqli("$host", "$username", "$password", "$db_name"); 

	// To protect MySQL injection (more detail about MySQL injection)
	$myusername = stripslashes($myusername);
	$myusername = $mysqli->real_escape_string($myusername);
	$sql = "INSERT INTO `members` (username, password) VALUES ('$myusername', '$mypassword');";
	$result = $mysqli->query($sql);
	$mysqli->close();
}
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
  cleanPost('user');
  cleanPost('pass');

  if(userAlreadyExists($_POST['user'])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'User already exists.';
  }
  else {
	registerUser($_POST['user'], $_POST['pass']);
    $userPrefs = array(
      'name' => $_POST['user']
    );
    echo json_encode($userPrefs);
  }
}
else {
  header('HTTP/1.1 404 Not Found');
  echo '404 page not found!'; // well you will have to make it prettier!
}
?>
