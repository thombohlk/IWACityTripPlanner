<?php

function makeVenueQuery($name, $location) {
    // Create query
    $query = "
	PREFIX iwa: <http://example.org/iwa/>
	PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
	PREFIX dc: <http://purl.org/dc/terms/>
	PREFIX fs: <https://api.foursquare.com/v2/venues/>

	SELECT DISTINCT ?id ?title ?lat ?lng ?address ?postalCode ?city WHERE {
	    ?id rdf:type iwa:Place .
	    ?id dc:title ?title .
	    ?id geo:lat ?lat .
	    ?id geo:long ?lng .
	    ?id iwa:PostalCode ?postalCode .
	    ?id iwa:Address ?address .
	    ?id geo:city ?city .
	    FILTER ( lang(?title) = 'nl' ) .
	    FILTER regex(?title, '$name', 'i' ) .

	} LIMIT 100
	";

	    // TODO Filter voor location? Hoedan?

	return $query;
}

function makeArtsHollandQuery($name, $activityType) {
	// Create query
	$query = "
		PREFIX ah: <http://purl.org/artsholland/1.0/>
		PREFIX iwa:<http://example.org/iwa/>
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

		SELECT DISTINCT ?event ?title ?eventTitle ?lat ?lng ?start ?end ?id WHERE {
			?event a ah:Event .
			?event ah:venue ?venue .
			?event time:hasBeginning ?start .
			?event time:hasEnd ?end .
			?event dc:title ?eventTitle .
			?event iwa:id ?id . 
			?venue dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?venue geo:lat ?lat .
			?venue geo:long ?lng .

		";

	if ($name != "") { 
		$query.= "FILTER regex(str(?title), '$name', 'i') .";
	}

	if ($activityType != "NoPref") {
		$query.= "?event iwa:eventType ah:VenueType".$activityType." . ";
	}

	/*
	if ($location != '') {
		$query .= "FILTER regex(str(?
	 */

	$query .= "} LIMIT 100";
	//print $query; exit();
	return $query;
}

function makeArtsHollandConstruct($name) {
	// Create query
	$query = "
		PREFIX ah: <http://purl.org/artsholland/1.0/>
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
		PREFIX iwa:<http://example.org/iwa/>

		CONSTRUCT {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Event dc:title ?EventTitle .
			?Event time:hasBeginning ?Start .
			?Event iwa:id ?Event . 
			?Event time:hasEnd ?End .
			?Venue dc:title ?title .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage ?Homepage . 
		} WHERE {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Venue dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?Event dc:title ?EventTitle .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Event time:hasBeginning ?Start .
			?Event time:hasEnd ?End .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage ?Homepage . 
		";

	if ($name != '') { 
		$query .= "FILTER regex(str(?title), '$name', 'i') .";
	}

	/*
	if ($location != '') {
		$query .= "FILTER regex(str(?
	 */

	$query .= "} LIMIT 100";

	return $query;
}

function makeHotelQuery($location) {
	$query = "
		PREFIX dc:<http://purl.org/dc/terms/>
		PREFIX onto:<http://www.ontotext.com/>
		PREFIX geo:<http://www.w3.org/2003/01/geo/wgs84_pos#>
		PREFIX foaf:<http://xmlns.com/foaf/0.1/>
		PREFIX vcard:<http://www.w3.org/2006/vcard/ns#>
		PREFIX gn:<http://www.geonames.org/ontology#>
		PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>
		PREFIX time:<http://www.w3.org/2006/time#>
		PREFIX search:<http://rdf.opensahara.com/search#>
		PREFIX osgeo:<http://rdf.opensahara.com/type/geo/>
		PREFIX iwa:<http://example.org/iwa/>
		PREFIX xsd:<http://www.w3.org/2001/XMLSchema#>
		PREFIX owl:<http://www.w3.org/2002/07/owl#>
		PREFIX fs:<https://api.foursquare.com/v2/venues/>
		PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
		PREFIX gr:<http://purl.org/goodrelations/v1#>
		PREFIX fn:<http://www.w3.org/2005/xpath-functions#>
		PREFIX ah:<http://purl.org/artsholland/1.0/>
		PREFIX bd:<http://www.bigdata.com/rdf/search#>
		PREFIX bigdata:<http://www.bigdata.com/rdf#>

		SELECT ?hotel ?title ?lat ?lng ?city ?id WHERE {
			?hotel a iwa:Hotel .
			?hotel dc:title ?title .
			?hotel geo:lat ?lat .
			?hotel geo:long ?lng .
			?hotel geo:city ?city .
			?hotel iwa:id ?id .

		";

	if ($name != '') { 
		$query .= "FILTER regex(str(?city), '$location', 'i') .";
	}

	/*
	if ($location != '') {
		$query .= "FILTER regex(str(?
	 */

	$query .= "} LIMIT 100";

	return $query;
}

function makeSearchActivityQuery($id) {
	// Create query
	$query = "
		PREFIX ah: <http://purl.org/artsholland/1.0/>
		PREFIX iwa:<http://example.org/iwa/>
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

		SELECT DISTINCT ?event ?title ?eventTitle ?lat ?lng ?start ?end ?id WHERE {
			<$id> rdf:type iwa:Place .
			?event a ah:Event .
			?event ah:venue <$id> .
			?event time:hasBeginning ?start .
			?event time:hasEnd ?end .
			?event dc:title ?eventTitle .
			?event iwa:id ?id . 
			<$id> dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			<$id> geo:lat ?lat .
			<$id> geo:long ?lng .
		} LIMIT 100";
		
	//print $query; exit();
	return $query;
}

?>
