<?php
	// Set headers
    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: application/json");
	
    try
    {
		$query = "PREFIX ah: <http://purl.org/artsholland/1.0/>
				PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
				PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
				PREFIX owl: <http://www.w3.org/2002/07/owl#>
				PREFIX dc: <http://purl.org/dc/terms/>
				PREFIX foaf: <http://xmlns.com/foaf/0.1/>
				PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
				PREFIX time: <http://www.w3.org/2006/time#>
				PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
				PREFIX vcard: <http://www.w3.org/2006/vcard/ns#>
				PREFIX osgeo: <http://rdf.opensahara.com/type/geo/>
				PREFIX bd: <http://www.bigdata.com/rdf/search#>
				PREFIX search: <http://rdf.opensahara.com/search#>
				PREFIX fn: <http://www.w3.org/2005/xpath-functions#>
				PREFIX gr: <http://purl.org/goodrelations/v1#>
				PREFIX gn: <http://www.geonames.org/ontology#>

				SELECT DISTINCT ?event ?eventStatus ?production ?room ?genre WHERE { {
				?event	a	ah:Event;
						ah:venue <http://data.artsholland.com/venue/4d23798b-a77b-4e5d-83a7-b91a5f223858>;
						ah:eventStatus	?eventStatus;
						ah:production	?production;
						ah:room	?room. }
				UNION
				{ ?production a ah:Production;
							ah:genre ?genre.
				}
			}
			LIMIT 100";
		
		$triples = sparqlQuery($query, "http://api.artsholland.com/sparql", "application/json");
		
		// Return data as JSON object
		//print "bla\n";
		var_dump($triples);
    }
    catch (Exception $e)
    {
        header("HTTP/1.0 500 Unexpected server error.");
		
		print $e->getMessage();

        exit();
    }
    

	function sparqlQuery($query, $baseURL, $format="application/json") {
		$params=array(
			"query" =>  $query,
			"api_key" => "9cbce178ed121b61a0797500d62cd440"
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
			header("HTTP/1.0 500 Unexpected server error.");
			print "No output was given.";
			exit();
		} else if($info['http_code'] != 200) {
			header("HTTP/1.0 500 Unexpected server error.");
			print "Error http: ".$info['http_code'].", curl error: ".$errno."\n";
			exit();
		}
		curl_close($ch);

		return $data;
	};
?>
