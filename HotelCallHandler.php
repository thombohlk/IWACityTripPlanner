<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");
	
	//Get the variables needed for the ArtsHolland call and check if they are filled
	$location = $_GET['location'];
	$startDate = $_GET['startDate'];
	$endDate = $_GET['endDate'];
	
	$startDate = date("m/d/Y", strtotime($startDate));
	$endDate = date("m/d/Y", strtotime($endDate));

	$hotelList = getHotelList($location);
	$rdfData = parseJSONtoRDF($hotelList);
	
	postData($rdfData, "application/x-turtle");
	
	$sesamequery = makeHotelQuery($location);
	//print $sesamequery; exit();
	
	$json = json_decode(getRDFData($sesamequery));
	
	$result = $json->{'results'}->{'bindings'};
	
	print json_encode($result);;
	
	
	
	function getHotelList($city) {
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
			'arrivalDate' => $startDate,
			'departureDate' => $endDate,
			'room1' => '1,3',
			'room2' => '1,5',
			'numberOfResults' => '100',
			'supplierCacheTolerance' => 'MED_ENHANCED'
		));

		// Perform curl command
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_URL, $url);

		$output = curl_exec($ch);

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
		curl_close($ch);
		
		$data = json_decode($output);
		$hotelList = $data->{'HotelListResponse'}->{'HotelList'}->{'HotelSummary'};
//		print $hotelList; exit();
//		var_dump(json_encode($hotelList)); exit();
		
		return $hotelList;
	}
	
	function parseJSONtoRDF($hotelList) {
		$output = '';
		
		$output.= "@prefix iwa: <http://example.org/iwa/> . ";
		$output.= "@prefix dc: <http://purl.org/dc/terms/> . ";
		$output.= "@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> . ";
		
		foreach ($hotelList as $key => &$hotel) {
			$output.= "iwa:".$hotel->{'hotelId'}." ";
			$output.= "rdf:type iwa:Hotel ; ";
			$output.= "dc:title \"".$hotel->{'name'}."\"@nl ; ";
			$output.= "geo:lat \"".$hotel->{'latitude'}."\" ; ";
			$output.= "geo:long \"".$hotel->{'longitude'}."\" ; ";
			$output.= "geo:city \"".$hotel->{'city'}."\" ; ";
			$output.= "iwa:id \"".$hotel->{'hotelId'}."\" . ";
		}
		
		return $output;
	}

?>
