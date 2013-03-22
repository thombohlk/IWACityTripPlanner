<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");

    // Define global variables for ArtsHolland call
    $api_key = '9cbce178ed121b61a0797500d62cd440';
    $endpoint = 'http://api.artsholland.com/sparql';
    $format = 'applicaton/json';

    // Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
    
    try {
		//Get the variables needed for the foursquare call and check if they are filled
		$venueId = $_GET['id'];
		$venueHomepage = $_GET['homepage'];
			
		if ($venueId == null) {
			header("HTTP/1.0 500 SearchActivityHandler.php: No id provided.");
			exit();
		}

		// Create ArtsHolland query, assert results into Sesame
		$query = makeArtsHollandVenueConstruct($venueId, $venueHomepage);
		$triples = executeQuery($query, $endpoint);
//print $triples; exit();

		postData($triples);
		
		// Query Sesame 
		$sesamequery = makeSearchActivityQuery($venueId);
		
		$json = json_decode(getRDFData($sesamequery));
		$result = $json->{'results'}->{'bindings'};
//print json_encode($result); exit();
		// Return data as JSON object
		print json_encode($result);
		
    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected server error.");	
	    print $e->getMessage();
        exit();
    }

	function executeQuery($query, $baseURL) {
		global $api_key;
		$params=array(
			"query" =>  $query,
			"api_key" => $api_key
		);

		// Create the url 
		$url = $baseURL.'?'.http_build_query($params);
		$header[0] = "Accept: application/sparql-results+json"; 

		// Perform curl command
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);;

		$output = curl_exec($ch);
		$data = json_decode($output);

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

		curl_close($ch);
		return $output;
	}
?>
