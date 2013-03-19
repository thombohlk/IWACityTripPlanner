<?php

    // Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
    
    try {
	//Get the variables needed for the foursquare call and check if they are filled
	$searchType = $_GET['searchType'];
		
	if ($searchType == null) {
	    header("HTTP/1.0 500 No search type provided.");
	    exit();
	}
	
	if ($searchType == "place") {
	    include("FoursquareCallHandler.php");
	} else if ($searchType == "activity") {
	    include("ArtsHollandCallHandler.php");
	} else if ($searchType == "hotel") {
	    include("HotelCallHandler.php");
	} else {
	    header("HTTP/1.0 500 Invalid search type provided.");
	    exit();
	}

    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected server error.");	
	    print $e->getMessage();
        exit();
    }
?>
