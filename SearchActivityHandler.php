<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");

    // Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
    
    try {
		//Get the variables needed for the foursquare call and check if they are filled
		$venueId = $_GET['id'];
		$venueHomepage = $_GET['homepage'];
		
		// Check if $venueId has been provided.
		if ($venueId == null) {
			header("HTTP/1.0 500 SearchActivityHandler.php: No id provided.");
			exit();
		}

		// Create ArtsHolland query, assert results into Sesame.
		$construct = createActivityConstructForVenue($venueId, $venueHomepage);
		$RDFdata = performArtsHollandConstruct($construct);
		postRDFtoSesame($RDFdata);
		
		// Create Sesame query for retrieving activity data, execute it and decode data.
		$sesamequery = makeSearchActivityQuery($venueId);
		$json = json_decode(getRDFfromSesame($sesamequery));
		$result = $json->{'results'}->{'bindings'};

		// Return data as JSON object
		print json_encode($result);
		
    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected server error.");	
	    print "Unexpected server error: ".$e->getMessage();
        exit();
    }

	// Performs ArtsHolland call to retrieve RDF data from $construct.
	function performArtsHollandConstruct($construct) {
		// Define global variables for ArtsHolland call
		$api_key = '9cbce178ed121b61a0797500d62cd440';
		$baseURL = 'http://api.artsholland.com/sparql';
		$format = 'application/sparql-results+json';
		
		// Define parameters.
		$params = array(
			"query" =>  $construct,
			"api_key" => $api_key
		);

		// Create the URL including parameters and header.
		$url = $baseURL.'?'.http_build_query($params);
		$header[0] = "Accept: ".$format; 

		// Setup curl call.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);;

		// Perform curl call and decode output.
		$output = curl_exec($ch);
		$data = json_decode($output);

		// Check if any errors occured during curl call and print if so.
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if( $output === false) {
			header("HTTP/1.0 500 Unexpected ArtsHolland server error.");
			print "No output was given.";
			exit();
		} else if ($info['http_code'] != 200) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Error http: ".$info['http_code'].", curl error: ".$errno."\n";

			exit();
		}	
		
		// Close curl call handler.
		curl_close($ch);

		// Return output.
		return $output;
	}
?>
