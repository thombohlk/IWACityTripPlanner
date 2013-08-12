<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>IWA - City Trip Planner</title>
        
        <link rel="stylesheet" href="style.css" />
        <script type="text/javascript" src="jquery-1.7.1.min.js" ></script> 
	<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css" />
    <script type="text/javascript" src="SearchCallHandler.js" ></script>
    <script type="text/javascript" src="GoogleMaps.js" ></script>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBmlvKKx2erMSAiaAveFJhG5NRmCrZOvmI&sensor=true"></script>
	<script type="text/javascript" src="HelperFunctions.js" ></script>
    </head>
    <body onload="initializeMap(); scaleWindow(); updateInputFields();">
        <div class="contentBox" id="searchBar">
			<h2><span class="inputHeader">Search input</span></h2>
			<div class="inputContent">
				<table>
					<colgroup>
						<col style="width: 100px">
						</colgroup>
					<tr>
						<td><span class="optionLabel">Search for:</span></td>
						<td>
							<select required id="searchType" onchange="updateInputFields();">
							<option value="place">Place</option>
							<option value="activity">Activity</option>
							<option value="hotel">Hotel</option>
							</select>
						</td>
					</tr>
					<tr class="placeClass activityClass hotelClass">
						<td><span class="optionLabel">Name</span></td>
						<td><input type="text" size="30" id="name" onkeydown="if (event.keyCode == 13) { getResults(); }" /></td>
					</tr>
					<tr class="placeClass activityClass hotelClass">
						<td><span class="optionLabel">Location</span></td>
						<td><input type="text" size="30" id="location" onkeydown="if (event.keyCode == 13) { getResults(); }" /></td>
					</tr>
					<tr class="activityClass hotelClass">
						<td><span class="optionLabel">Starting date</span></td>
						<td><input type="text" id="startdatepicker" value=""/></td>
					</tr>
					<tr class="activityClass hotelClass">
						<td><span class="optionLabel">End date</span></td>
						<td><input type="text" id="enddatepicker" value=""/></td>
					</tr>
					<tr class="activityClass">
						<td><span class="optionLabel">Type of activity</span></td>
						<td>
							<select class="activityClass" id="activityType">
								<option value="NoPref">No preference</option>
								<option value="Outdoorlocation">Outdoor location</option>
								<option value="Theater">Theater</option>
								<option value="Museum">Museum</option>
								<option value="Gallery">Gallery</option>
								<option value="Cinema">Cinema</option>
								<option value="Architecture">Architecture</option>
								<option value="Landmark">Landmark</option>
								<option value="Dance">Dance</option>
								<option value="Hotel">Hotel</option>
								<option value="Music">Music</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2 style="text-align: center;"><input type="button" value="Search" onclick="getResults();"/></td>
					</tr>
				</table>
			</div>
        </div>


		<div class="contentBox" id="resultListBox">
			<h2><span class="inputHeader">Search results</span></h2>
			<div id="resultList" class="resultList">	
			</div>
		</div> 


		<div id="map_canvas"></div>


		<div id="timelineBox">
			<div id="timelineTitleBar">
				<span class="timelineTitle">Timeline</span>
				<span class="timelineDesc">Travel mode</span>
				<select id="timelineTravelMode" onchange="calcRoute();">
					<option value="WALKING">Walking</option>
					<option value="BICYCLING">Bicycling</option>
					<option value="TRANSIT">Transit</option>
					<option value="DRIVING">Driving</option>
				</select>
				<span class="timelineDesc">Duration</span>
				<span id="timelineDuration" class="timelineValue">-</span>
				<span class="timelineDesc">Distance</span>
				<span id="timelineDistance" class="timelineValue">-</span>
			</div>
			<div id="saveButton" class="timelineButton"><input type="button" value="Save trip" title="Save trip" onclick="saveCityTrip();"/></div>
<?php
session_start();
if (isset($_SESSION['userid'])) {
	echo '<div id="loginButton" class="hidden timelineButton"><input type="button" value="Login" title="Login" onclick="showLoginPopup();"/></div>';
	echo '<div id="logoutButton" class="timelineButton"><input type="button" value="Logout" title="Logout" onclick="logout();"/></div>';
} else {
	echo '<div id="loginButton" class="timelineButton"><input type="button" value="Login" title="Login" onclick="showLoginPopup();"/></div>';
	echo '<div id="logoutButton" class="hidden timelineButton"><input type="button" value="Logout" title="Logout" onclick="logout();"/></div>';
}
?>
			<div id="resetButton" class="timelineButton"><input type="button" value="Reset repository" title="Click here to reset the repository and start with a clean database." onclick="resetRepository();"/></div>
			<div id="trashcan" class="trashcan"></div>
			<div id="timeline" class="timeline"></div>
		</div> 
    
    </body>
    
    <div class="modal"></div>
    <div id="message" class="popup hidden">Message</div>
    <div id="loginPopup" class="popup hidden">
	  <h1>Login Form</h1>
	  <form id="loginForm">
		<div>
		  <label for="user">Username: </label>
		  <input type="text" name="user" id="user" />
		</div>
		<div>
		  <label for="pass">Password: </label>
		  <input type="pass" name="pass" id="pass" />
		</div>
		<div>
		  <input type="button" id="login" name="login" value="Login" />
		</div>
		<a onClick="switchToSignUpPopup();">No account yet?</a>
	  </form>
	</div>
    <div id="signUpPopup" class="popup hidden">
	  <h1>Sign up form</h1>
	  <form id="signUpForm">
		<div>
		  <label for="user">Username: </label>
		  <input type="text" name="user" id="user" />
		</div>
		<div>
		  <label for="pass">Password: </label>
		  <input type="pass" name="pass" id="pass" />
		</div>
		<div>
		  <input type="button" id="signUp" name="signUp" value="Sign up" />
		</div>
	  </form>
	</div>
</html>
