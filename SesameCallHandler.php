<?php

	include("SesameFunctions.php");

    // Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
    
    try {
	//Get the variables needed for the foursquare call and check if they are filled
	$call = $_GET['call'];
		
	if ($call == null) {
	    header("HTTP/1.0 500 No search type provided.");
	    exit();
	}

	if ($call == "reset") {
		resetRepository();
	} else {
	    header("HTTP/1.0 500 Invalid search type provided.");
	    exit();
	}

    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected openRDF-Sesame server error.");	
	    print $e->getMessage();
        exit();
    }
?>
