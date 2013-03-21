<?php

// Foursquare venue query
function makeVenueQuery($name, $location) {
    // Create query
    $query = "
	PREFIX iwa: <http://example.org/iwa/>
	PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
	PREFIX dc: <http://purl.org/dc/terms/>
	PREFIX fs: <https://api.foursquare.com/v2/venues/>

	SELECT DISTINCT ?venue ?id ?title ?lat ?lng ?address ?postalCode ?city WHERE {
	    ?venue rdf:type iwa:Place .
		?venue iwa:id ?id .
	    ?venue dc:title ?title .
	    ?venue geo:lat ?lat .
	    ?venue geo:long ?lng .
	    ?venue iwa:PostalCode ?postalCode .
	    ?venue iwa:Address ?address .
	    ?venue geo:city ?city .
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

		SELECT DISTINCT ?event ?venueTitle ?title ?lat ?lng ?start ?end ?id ?venueId ?sameAsVenueId ?description WHERE {
			?event a ah:Event .
			?event ah:venue ?venue .
			?event time:hasBeginning ?start .
			?event time:hasEnd ?end .
			?event dc:title ?title .
			?event iwa:id ?id . 
			OPTIONAL { ?event dc:description ?description . }
			?venue dc:title ?venueTitle .
			?venue iwa:id ?venueId .
			FILTER ( lang(?title) = 'nl' ) .
			?venue geo:lat ?lat .
			?venue geo:long ?lng .
			?venue owl:sameAs ?sameAs .
			?sameAs iwa:id ?sameAsVenueId .
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
		PREFIX iwa: <http://example.org/iwa/>

		CONSTRUCT {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Event dc:title ?EventTitle .
			?Event time:hasBeginning ?Start .
			?Event iwa:id ?EventId . 
			?Event time:hasEnd ?End .
			?Event dc:description ?description .
			?Venue dc:title ?title .
			?Venue iwa:id ?VenueId .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage ?Homepage . 
		} WHERE { {
			?Event a ah:Event .
			?Event ah:cidn ?EventId .
			?Event ah:venue ?Venue .
			?Event dc:title ?EventTitle .
			?Event time:hasBeginning ?Start .
			?Event time:hasEnd ?End .
			?Venue dc:title ?title .
			?Venue ah:cidn ?VenueId .
			FILTER ( lang(?title) = 'nl' ) .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage ?Homepage . 
		";

	if ($name != '') { 
		$query .= "FILTER regex(str(?title), '$name', 'i') .";
	}
		$query.= "} UNION {

			?Event a ah:Event .
			?Event ah:cidn ?EventId .
			?Event ah:venue ?Venue .
			?Event time:hasBeginning ?Start .
			?Event time:hasEnd ?End .
			?Event ah:production ?Production .
			?Production dc:title ?EventTitle .
			OPTIONAL { ?Production dc:description ?description . }
			?Venue dc:title ?title .
			?Venue ah:cidn ?VenueId .
			FILTER ( lang(?title) = 'nl' ) .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage ?Homepage .
		";

	if ($name != '') { 
		$query .= "FILTER regex(str(?EventTitle), '$name', 'i') .";
	}

	/*
	if ($location != '') {
		$query .= "FILTER regex(str(?
	 */

	$query .= "}} LIMIT 100";
//print $query; exit();
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
			FILTER ( lang(?title) = 'nl' ) .
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
			?place rdf:type iwa:Place .
			?place iwa:id \"$id\" .
			?event a ah:Event .
			?event ah:venue ?place .
			?event time:hasBeginning ?start .
			?event time:hasEnd ?end .
			?event dc:title ?eventTitle .
			?event iwa:id ?id . 
			?place dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?place geo:lat ?lat .
			?place geo:long ?lng .
		} LIMIT 100";
		
	//print $query; exit();
	return $query;
}

?>
