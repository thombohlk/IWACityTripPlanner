<?php
	// Include files
	include("SesameFunctions.php");
	include("Queries.php");

	// Define global variables for foursquare call
    $client_id = 'PDIS2NTFLWTMYOVHUA3T2FKDEXXJ00FRTBH0H1SZPOASX5OI';
    $client_secret = 'RVTQNR1PO4H0GMSGCAAHVYFRYAM2PMQHASXWSXZWSXWCLFIL';
	
    try
    {
	//Get the variables needed for the foursquare call and check if they are filled
	$location = $_GET['location'];
	$name = $_GET['name'];
		
	if ($location == '' || $name == '') {
	    header("HTTP/1.0 500 Name or location is not provided.");
	    exit();
        }

	// Create the url 
        $url = 'https://api.foursquare.com/v2/venues/search?'.http_build_query(array(
			'near' => $location,
			'query' => $name,
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'v' => '20130219',
		));

        // Perform curl command
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_URL, $url);

		$output = curl_exec($ch);
		$data = json_decode($output);

		$info = curl_getinfo($ch);
		$errno = curl_errno($ch);
		if( $output === false) {
			header("HTTP/1.0 500 Unexpected Foursquare server error.");
			print "No output was given.";
			exit();
		} else if ($data != null && $data->{'meta'}->{'code'} != 200) {	
			header("HTTP/1.0 ".$data->{'meta'}->{'code'}." ".$data->{'meta'}->{'errorDetail'});
			exit();
		} else if ($info['http_code'] != 200) {
			header("HTTP/1.0 ".$info['http_code']." ".$errno);
			print "Error http: ".$info['http_code'].", curl error: ".$errno."\n";
			exit();
		}
		curl_close($ch);
		
		// Remove unnecessary data
		$venues = $data->{'response'}->{'venues'};
		foreach ($venues as $key => &$venue) {
			unset($venue->{'canonicalUrl'});
			unset($venue->{'categories'});
			unset($venue->{'verified'});
			unset($venue->{'stats'});
			unset($venue->{'likes'});
			unset($venue->{'specials'});
			unset($venue->{'hereNow'});
			unset($venue->{'referralId'});
			unset($venue->{'restricted'});
		}

		// Assert results into Sesame repository
		$triples = parseJSONtoRDF($venues);
		postData($triples, "application/x-turtle");

		// Query Sesame repository
		$sesamequery = makeVenueQuery($name, $location); 
		$json = json_decode(getRDFData($sesamequery));
		$result = $json->{'results'}->{'bindings'};

		// Return data as JSON object
		print json_encode($result);

    }
    catch (Exception $e)
    {
        header("HTTP/1.0 500 Unexpected server error.");
		print $e->getMessage();
		exit();
    }


	function parseJSONtoRDF($venues) {
		$output = '';

		$output .= "@prefix iwa: <http://example.org/iwa/> . ";
		$output .= "@prefix dc: <http://purl.org/dc/terms/> . ";
		$output .= "@prefix fs: <https://api.foursquare.com/v2/venues/> . ";
		$output .= "@prefix geo: <http://www.w3.org/2003/01/geo/wgs84_pos#> . ";

		foreach ($venues as $key => &$venue) {
			$id = "fs:" . $venue->{'id'};
			$output .= $id . " rdf:type iwa:Place . ";
			$output .= $id . " dc:title \"" . $venue->{'name'} . "\"@nl . ";
			$output .= $id . " geo:lat \"" . $venue->{'location'}->{'lat'} . "\" . ";
			$output .= $id . " geo:long \"" . $venue->{'location'}->{'lng'} . "\" . ";

			if (isset($venue->{'location'}->{'address'})) {
				$output .= $id . " iwa:Address \"" . $venue->{'location'}->{'address'} . "\" . ";
			} else {
				$output .= $id . " iwa:Address \"undefined\" . ";
			}

			if (isset($venue->{'location'}->{'postalCode'})) {
				$output .= $id . " iwa:PostalCode \"" . $venue->{'location'}->{'postalCode'} . "\" . ";
			} else {
				$output .= $id . " iwa:PostalCode \"undefined\" . ";
			}

			if (isset($venue->{'location'}->{'city'})) {
				$output .= $id . " geo:city \"" . $venue->{'location'}->{'city'} . "\" . ";
			} else {
				$output .= $id . " geo:city \"undefined\" . ";
			}
		}
		return $output;
	}

?>
