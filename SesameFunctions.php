<?php
	function postData($triples, $format="application/xml") {
	
		// Create the url 
		$url = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner/statements";
		$header[0] = "Content-Type: ".$format.";charset=utf-8"; 
	
		//set the url, number of POST vars, POST data
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $triples); 

		$output = curl_exec($ch);

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

		curl_close($ch);
	}

	function getRDFData($query) {
		$output = "";
		// Create the url 
		$baseURL = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner";
		$params = array(
			"query" =>  $query,
			"queryLn" => "sparql"
		);
	
		// Create the url 
		$url = $baseURL.'?'.http_build_query($params);
		$header[0] = "Accept: application/sparql-results+json";
	
		//set the url, number of POST vars, POST data
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);

		$output = curl_exec($ch);

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

		curl_close($ch);

		return $output;
	}

	function resetRepository() {
		clearRepository();

		$file = "inferenceRules.ttl";
		$fh = fopen($file, 'r');
		$rules = fread($fh, filesize($file));
		fclose($fh);

		postData($rules, "application/x-turtle");
	}

	function insertSameCityTriples() {
		$query = makeSameCityInsert();

		// Create the url 
		$baseURL = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner/statements";
		$params = array(
			"update" => $query
		);
	
		// Create the url 
		$header[0] = "Content-Type:application/x-www-form-urlencoded"; 
		$header[1] = "Accept: */*";

		//set the url, number of POST vars, POST data
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $baseURL);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

		$output = curl_exec($ch);
		
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

		curl_close($ch);
	}

	function clearRepository() {

		// Create the url 
		$url = "http://77.250.167.72:8080/openrdf-sesame/repositories/CityTripPlanner/statements";
		$header[0] = "Content-Type:application/x-www-form-urlencoded"; 
	
		//set the url, number of POST vars, POST data
		$ch = curl_init();
		//curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $triples); 

		$output = curl_exec($ch);

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

		curl_close($ch);
	}
?>
