<?php

    // Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
    
    try {
		//Get the variables needed for the foursquare call and check if they are filled
		$searchType = $_GET['searchType'];
	
		// Check if search type is provided.	
		if ($searchType == null) {
			header("HTTP/1.0 500 No search type provided.");
			print "No search type has been provided.";
			exit();
		}

		// Depending on the search type, call the proper PHP file to process the call.
		if ($searchType == "place") {
			include("VenueCallHandler.php");
		} else if ($searchType == "activity") {
			include("ActivityCallHandler.php");
		} else if ($searchType == "hotel") {
			include("HotelCallHandler.php");
		} else {
			header("HTTP/1.0 500 Invalid search type provided.");
			print "Invalid search type has been provided.";
			exit();
		}
    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected server error.");	
	    print "Unexpected server error: ".$e->getMessage();
        exit();
    }
?>
