<?php

	// Posts RDF data to the Sesame repository.
	function postRDFtoSesame($RDFdata, $format="application/xml") {
	
		// Create the url and headers.
		$url = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner/statements";
		$header[0] = "Content-Type: ".$format.";charset=utf-8"; 
	
		// Setup curl call handler.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $RDFdata); 

		// Perform curl call.
		$output = curl_exec($ch);

		// Check if any errors occured and print error message if so.
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if( $output === false) {
			header("HTTP/1.0 500 Unexpected openRDF-Sesame error.");
			print "No output was given.";
			exit();
		} else if ($info['http_code'] != 204) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Postdata error http: ".$info['http_code'].", curl error: ".$errno."\n";
			exit();
		}

		// Close curl call handler.
		curl_close($ch);
	}

	// Gets RDF data from the Sesame repository using $query.
	function getRDFfromSesame($query) {
		$output = "";

		// Define baseURL and parameters.
		$baseURL = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner";
		$params = array(
			"query" =>  $query,
			"queryLn" => "sparql"
		);
	
		// Create the url including parameters.
		$url = $baseURL.'?'.http_build_query($params);
		$header[0] = "Accept: application/sparql-results+json";
	
		// Setup curl call handler.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);

		// Perform curl call.
		$output = curl_exec($ch);

		// Check if any errors occured and print error message if so.
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if( $output === false) {
			header("HTTP/1.0 500 Unexpected openRDF-Sesame error.");
			print "No output was given.";
			exit();
		} else if ($info['http_code'] != 200) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Get RDF data error http: ".$info['http_code'].", curl error: ".$errno."\n";
			print $output;
			exit();
		}

		// Close curl call handler.
		curl_close($ch);

		// Return output.
		return $output;
	}

	// Performs INSERT query on Sesame repository.
	function insertSameCityTriples() {
		$query = makeSameCityInsert();

		// Define baseURL and parameters.
		$baseURL = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner/statements";
		$params = array(
			"update" => $query
		);
	
		// Create the headers.
		$header[0] = "Content-Type:application/x-www-form-urlencoded"; 
		$header[1] = "Accept: */*";

		// Setup curl call handler.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $baseURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

		// Perform curl call.
		$output = curl_exec($ch);
		
		// Check if any errors occured and print error message if so.
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);

		if( $output === false) {
			header("HTTP/1.0 500 Unexpected openRDF-Sesame error.");
			print "No output was given.";
			exit();
		} else if ($info['http_code'] != 204) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Get RDF data error http: ".$info['http_code'].", curl error: ".$errno."\n";
			exit();
		}

		// Close curl call handler.
		curl_close($ch);
	}

	// Resets Sesame reppository by clearing existing data and inserting standard inference rules.
	function resetRepository() {
		// Clear repository.
		clearRepository();

		// Read standard inference rules from file and post them.
		$file = "inferenceRules.ttl";
		$fh = fopen($file, 'r');
		$rules = fread($fh, filesize($file));
		fclose($fh);

		postRDFtoSesame($rules, "application/x-turtle");
	}

	// Deletes all the data currently present in the Sesame repository.
	function clearRepository() {
		// Create the url and headers.
		$url = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner/statements";
		$header[0] = "Content-Type:application/x-www-form-urlencoded"; 
	
		// Setup curl call handler.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 

		// Perform curl call.
		$output = curl_exec($ch);

		// Check if any errors occured and print error message if so.
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if( $output === false) {
			header("HTTP/1.0 500 Unexpected openRDF-Sesame error.");
			print "No output was given.";
			exit();
		} else if ($info['http_code'] != 204) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Clear repository http error: ".$info['http_code'].", curl error: ".$errno."\n";
			exit();
		}

		// Close curl call handler.
		curl_close($ch);
	}
?>
