<?php
	// Include files
	include("SesameFunctions.php");
	include("query.php");

    // Define global variables for ArtsHolland call
    $api_key = '9cbce178ed121b61a0797500d62cd440';
    $endpoint = 'http://api.artsholland.com/sparql';
    $format = 'applicaton/json';

   try
    {
		//Get the variables needed for the ArtsHolland call and check if they are filled
		$name = $_GET['name'];
		
		if ($name == '') {
			header("HTTP/1.0 500 Name is not provided.");
			exit();
		}

		// Create query
		$query = makeArtsHollandConstruct($name);
	
		$triples = sparqlQuery($query, $endpoint);
		
		postData($triples);
		$sesamequery = makeArtsHollandQuery($name);

		$json = json_decode(getRDFData($sesamequery));
		
		$result = $json->{'results'}->{'bindings'};
		//var_dump($json);
		print json_encode($result);
		// Return data as JSON object
		//print $triples;
    }
    catch (Exception $e)
    {
        header("HTTP/1.0 500 3 Unexpected server error.");
		print $e->getMessage();
        exit();
    }

	function sparqlQuery($query, $baseURL) {
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
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_URL, $url);

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
		//print "bla1\n";

		//var_dump($data);
	}
?>
