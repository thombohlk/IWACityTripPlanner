<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");

	try	{
		//Get the variables needed for the ArtsHolland call and check if they are filled
		$name = $_GET['name'];
		$location = $_GET['location'];
		$activityType = $_GET['activityType'];
		$startDate = $_GET['startDate'];
		$endDate = $_GET['endDate'];

		// Convert input dates to proper format if present.
		if ($startDate != "") $startDate = date("Y-m-d", strtotime($startDate))."T00:00:00Z";
		if ($endDate != "") $endDate = date("Y-m-d", strtotime($endDate))."T23:59:59Z";
		
		// Check if either location or name have been given.
		if ($location == '' && $name == '') {
			header("HTTP/1.0 500 ArtsHollandCallHandler.php: location nor name specified.");
			exit();
		}
		
		// Check if searchtype has been provided.
		if ($activityType == '') {
			header("HTTP/1.0 500 ArtsHollandCallhandler.php: activityType not specified.");
			exit();
		}

		// Create ArtsHolland query, assert results into Sesame
		$AHquery = createActivityConstruct($name, $location, $activityType);
		$AHdata = executeArtsHollandQuery($AHquery);
		postRDFtoSesame($AHdata);
		
		// Create query to search for activities in Sesame and strip the data that is returned.
		$sesameQuery = createActivityQuery($name, $location, $activityType, $startDate, $endDate);
		$json = json_decode(getRDFfromSesame($sesameQuery));
		$results = $json->{'results'}->{'bindings'};
		
		// Return data as JSON object
		print json_encode($results);
		
    } catch (Exception $e) {
        header("HTTP/1.0 500 3 Unexpected server error.");
		print $e->getMessage();
        exit();
    }

	// Retrieves results for ArtsHolland call using $query.
	function executeArtsHollandQuery($query) {
		// Define variables for ArtsHolland call.
		$api_key = '9cbce178ed121b61a0797500d62cd440';
		$baseURL = 'http://api.artsholland.com/sparql';
		$format = 'applicaton/json';

		// Define parameter array.
		$params=array(
			"query" =>  $query,
			"api_key" => $api_key
		);

		// Create the url.
		$url = $baseURL.'?'.http_build_query($params);
		$header[0] = "Accept: application/sparql-results+json"; 

		// Setup curl call.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_URL, $url);;

		// Execute curl call and decode data.
		$output = curl_exec($ch);
		$data = json_decode($output);

		// Check for errors and print error information if there is any.
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

		// Close the curl session and return the output.
		curl_close($ch);
		return $output;
	}
?>
