<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");

	try {
		//Get the variables needed for the ArtsHolland call.
		$location = $_GET['location'];
		$id = $_GET['id'];

		// Perform call to EAN using $location and parse output to RDF.
		$data = performEANCall($location);
		$RDFdata = parseJSONtoRDF($data);

		// Post RDF data to sesame and perform INSERT to create sameCityAs relations.
		postRDFtoSesame($RDFdata, "application/x-turtle");
		insertSameCityTriples();

		// Create query to find hotels in same city as $id, execute it and parse output.
		$sesamequery = createHotelInSameCityQuery($id);
		$json = json_decode(getRDFfromSesame($sesamequery));
		$results = $json->{'results'}->{'bindings'};

		// Return results.
		print json_encode($results);
		
    } catch (Exception $e) {
        header("HTTP/1.0 500 Unexpected server error.");	
	    print "Unexpected server error: ".$e->getMessage();
        exit();
    }
	
	// Perform call to Expedia Affiliate Network for hotels in $city.
	function performEANCall($city) {
		// Create URL including user input.
		$ip = $_SERVER['REMOTE_ADDR'];
		$url = 'http://api.ean.com/ean-services/rs/hotel/v3/list?'.http_build_query(array(
			'minorRev' => '1',
			'cid' => '55505',
			'apiKey' => 'jht2husvz42j5w7g24qsjg5q',
			'customerUserAgent' => '',
			'customerIpAddress' => $ip,
			'currencyCode' => 'EUR',
			'destinationString' => $city,
			'countryCode' => 'NL',
			'supplierCacheTolerance' => 'MED',
			'arrivalDate' => "",
			'departureDate' => "",
			'room1' => '1,3',
			'room2' => '1,5',
			'numberOfResults' => '100',
			'supplierCacheTolerance' => 'MED_ENHANCED'
		));
		
		// Setup curl call.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_URL, $url);

		// Execute curl call.
		$output = curl_exec($ch);

		// Check if any errors occured during the curl call and execute.
		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if( $output === false) {
			header("HTTP/1.0 500 Unexpected EAN server error.");
			print "No output was given.";
			exit();
		} else if ($info['http_code'] != 200) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Error http: ".$info['http_code'].", curl error: ".$errno."\n";
			exit();
		}

		// Close curl call handler.
		curl_close($ch);
		
		// Decode and parse output.
		$data = json_decode($output);
		$results = $data->{'HotelListResponse'}->{'HotelList'}->{'HotelSummary'};

		// Return results.		
		return $results;
	}
	
	function parseJSONtoRDF($hotelList, $startDate, $endDate) {
		// Define prefixes.
		$output = "@prefix iwa: <http://example.org/iwa/> . ";
		$output.= "@prefix dc: <http://purl.org/dc/terms/> . ";
		$output.= "@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> . ";

		// Create $search and $replace to remove unwanted characters from data.
		$search = array("'", "\"");
		$replace = "";
		
		// For each result lookup the needed properties and define it in RDF format.
		foreach ($hotelList as $key => &$hotel) {
			$output.= "iwa:".$hotel->{'hotelId'}." ";
			$output.= "rdf:type iwa:Hotel ; ";
			$output.= "dc:title \"".str_replace($search, $replace, $hotel->{'name'})."\"@nl ; ";
			$output.= "geo:lat \"".$hotel->{'latitude'}."\" ; ";
			$output.= "geo:long \"".$hotel->{'longitude'}."\" ; ";
			$output.= "iwa:id \"".$hotel->{'hotelId'}."\" ";
			if ($hotel->{'city'}) $output.= "; geo:city \"".str_replace($search, $replace, $hotel->{'city'})."\" ";
			if ($hotel->{'address1'}) $output.= "; iwa:Address \"".str_replace($search, $replace, $hotel->{'address1'})."\" ";
			if ($hotel->{'hotelRating'}) $output.= "; iwa:rating \"".$hotel->{'hotelRating'}."\" ";
			if ($hotel->{'shortDescription'}) $output.= "; dc:description \"".str_replace($search, $replace, $hotel->{'shortDescription'})."\" ";
			if ($hotel->{'highRate'}) $output.= "; iwa:highRate \"".$hotel->{'highRate'}."\" ";
			if ($hotel->{'lowRate'}) $output.= "; iwa:lowRate \"".$hotel->{'lowRate'}."\" ";
			$output.= ". ";
		}

		// Return output.
		return $output;
	}

?>
