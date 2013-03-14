<?php
    // Define global variables for foursquare call
    define('CLIENT_ID', 'PDIS2NTFLWTMYOVHUA3T2FKDEXXJ00FRTBH0H1SZPOASX5OI');
    define('CLIENT_SECRET', 'RVTQNR1PO4H0GMSGCAAHVYFRYAM2PMQHASXWSXZWSXWCLFIL');
	
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
	    'client_id' => 'PDIS2NTFLWTMYOVHUA3T2FKDEXXJ00FRTBH0H1SZPOASX5OI',
	    'client_secret' => 'RVTQNR1PO4H0GMSGCAAHVYFRYAM2PMQHASXWSXZWSXWCLFIL',
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
		
		// Return data as JSON object
		print json_encode($venues);

    }
    catch (Exception $e)
    {
        header("HTTP/1.0 500 Unexpected server error.");
		
		print $e->getMessage();

        exit();
    }
?>
