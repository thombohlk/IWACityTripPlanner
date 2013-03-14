<?php
	
function makeArtsHollandQuery($name) {
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

		SELECT DISTINCT ?Event ?VenueTitle ?EventTitle ?Lat ?Long ?Start ?End WHERE {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Venue dc:title ?VenueTitle .
			FILTER ( lang(?VenueTitle) = 'nl' ) .
			?Event dc:title ?EventTitle .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Event time:hasBeginning ?Start .
			?Event time:hasEnd ?End .

		";

	if ($name != '') { 
		$query .= "FILTER regex(str(?VenueTitle), '$name', 'i') .";
	}

	/*
	if ($location != '') {
		$query .= "FILTER regex(str(?
	 */

	$query .= "} LIMIT 100";

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

		CONSTRUCT {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Venue dc:title ?VenueTitle .
			?Event dc:title ?EventTitle .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Event time:hasBeginning ?Start .
			?Event time:hasEnd ?End .
		} WHERE {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Venue dc:title ?VenueTitle .
			FILTER ( lang(?VenueTitle) = 'nl' ) .
			?Event dc:title ?EventTitle .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Event time:hasBeginning ?Start .
			?Event time:hasEnd ?End .

		";

	if ($name != '') { 
		$query .= "FILTER regex(str(?VenueTitle), '$name', 'i') .";
	}

	/*
	if ($location != '') {
		$query .= "FILTER regex(str(?
	 */

	$query .= "} LIMIT 100";

	return $query;
}

?>
