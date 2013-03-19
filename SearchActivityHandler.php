<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");

    // Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
    
    try {
		//Get the variables needed for the foursquare call and check if they are filled
		$id = $_GET['id'];
			
		if ($id == null) {
			header("HTTP/1.0 500 SearchActivityHandler: No id provided.");
			exit();
		}
		
		// Query Sesame 
		$sesamequery = makeSearchActivityQuery($id);
		
		//print $sesamequery; exit();
		
		$json = json_decode(getRDFData($sesamequery));
		$result = $json->{'results'}->{'bindings'};

		// Return data as JSON object
		print json_encode($result);
		
    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected server error.");	
	    print $e->getMessage();
        exit();
    }
?>
