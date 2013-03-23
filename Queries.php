<?php

// Global variable with all prefixes needed in queries and constructs.
$prefixes = "PREFIX ah: <http://purl.org/artsholland/1.0/>
		PREFIX iwa: <http://example.org/iwa/>
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
		PREFIX gn: <http://www.geonames.org/ontology#>";

function makeSameCityInsert() {
    global $prefixes;

    $query = $prefixes."
	INSERT {
	    ?a iwa:sameCityAs ?b .
	} WHERE {
	    ?a geo:city ?city .
	    ?b geo:city ?city .
	}";

    return $query;
}

// Foursquare venue query
function makeVenueQuery($name, $location) {
	global $prefixes;

	// Create query
	$query = $prefixes."

	SELECT DISTINCT ?venue ?id ?title ?lat ?lng ?address ?postalCode ?city ?homepage WHERE {
		?venue rdf:type iwa:Place .
		?venue iwa:id ?id .
		?venue dc:title ?title .
		?venue geo:lat ?lat .
		?venue geo:long ?lng .
		?venue iwa:PostalCode ?postalCode .
		?venue iwa:Address ?address .
		?venue geo:city ?city .
		OPTIONAL { ?venue foaf:homepage ?homepage . } .
		FILTER ( lang(?title) = 'nl' ) .
		FILTER regex(?title, '$name', 'i' ) .
		FILTER regex(?city, '$location', 'i' ) .
	} LIMIT 100";

	return $query;
}

// Query for searching activities in Arts Holland repository
function makeArtsHollandQuery($name, $location, $activityType, $startDate, $endDate) {
	global $prefixes;

	$query = $prefixes."		

		SELECT DISTINCT ?event ?venueTitle ?title ?lat ?lng ?start ?end ?id ?venueId ?sameAsVenueId ?description ?location WHERE {
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
			OPTIONAL { ?venue owl:sameAs ?sameAs .
			?sameAs iwa:id ?sameAsVenueId . }
			?venue ah:locationAddress ?locAdd . 
			?locAdd vcard:locality ?location . 
		";

	// Depending on the availebility of $name and $location, a combination of filters is added.
	if ($name != "" && $location == "") {
		$query .= "FILTER (regex(str(?title), '$name', 'i') || regex(str(?venueTitle), '$name', 'i')) . 
			";
	} else if ($name == "" && $location != "") {
		$query .= "FILTER (regex(str(?venueTitle), '$location', 'i') || regex(str(?location), '$location', 'i')) . 
			";
	} else if ($name != "" && $location != "") {
		$query .= "FILTER ((regex(str(?venueTitle), '$name', 'i') && regex(str(?location), '$location', 'i')) ||
							(regex(str(?title), '$name', 'i') && regex(str(?location), '$location', 'i')) ||
							(regex(str(?title), '$name', 'i') && regex(str(?venueTitle), '$location', 'i'))) . 
			";
	}

	// Set filter for start and end date if present.
	if ($startDate != "") $query .= "FILTER (xsd:dateTime(?start) >= \"$startDate\"^^xsd:dateTime) . ";
	if ($endDate != "") $query .= "FILTER (xsd:dateTime(?end) <= \"$endDate\"^^xsd:dateTime) . ";
	
	// Set filter for activity type if present.
	//if ($activityType != "NoPref") $query .= "?event iwa:eventType ah:VenueType".$activityType." .	";
	if ($activityType != "NoPref") $query .= "?event a iwa:".$activityType."Event .	";

	$query .= "} LIMIT 100";

	return $query;
}

// Construct for retreiving RDF data from Arts Holland endpoint.
function makeArtsHollandConstruct($name, $location, $activityType) {
	global $prefixes;

	/* Create construct with a union. The union is needed as the title of an
	event can come from either the event or the production of the event. */
	$query = $prefixes."

		CONSTRUCT {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Event dc:title ?EventTitle .
			?Event time:hasBeginning ?Start .
			?Event iwa:id ?EventId . 
			?Event time:hasEnd ?End .
			?Event dc:description ?description .
			?Event iwa:eventGenre ?EventGenre .
			?Venue dc:title ?VenueTitle .
			?Venue iwa:id ?VenueId .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Venue geo:city ?Locality .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage ?Homepage .
			?Venue ah:locationAddress ?LocAddr .
			?LocAddr vcard:locality ?Locality .
			?LocAddr vcard:postal-code ?PostalCode .
			?LocAddr vcard:street-address ?Address .	

		} WHERE { {
				?Event a ah:Event .
				?Event ah:cidn ?EventId .
				?Event ah:venue ?Venue .
				?Event dc:title ?EventTitle .
				?Event time:hasBeginning ?Start .
				?Event time:hasEnd ?End .
				?Event ah:production ?Production .
				?Production ah:genre ?EventGenre .
				OPTIONAL { ?Event dc:description ?description . }
				?Venue dc:title ?VenueTitle .
				?Venue ah:cidn ?VenueId .
				FILTER ( lang(?VenueTitle) = 'nl' ) .
				?Venue geo:lat ?Lat .
				?Venue geo:long ?Long .
				?Venue ah:venueType ?VenueType .
				?Venue foaf:homepage ?Homepage . 
				?Venue ah:locationAddress ?LocAddr .
				?LocAddr vcard:locality ?Locality .
				?LocAddr vcard:postal-code ?PostalCode .
				?LocAddr vcard:street-address ?Address .	
		";

	// Depending on the availebility of $name and $location, a combination of filters is added.
	if ($name != "" && $location == "") {
		$query .= "FILTER (regex(str(?EventTitle), '$name', 'i') || regex(str(?VenueTitle), '$name', 'i')) . 
			";
	} else if ($name == "" && $location != "") {
		$query .= "FILTER (regex(str(?VenueTitle), '$location', 'i') || regex(str(?Locality), '$location', 'i')) . 
			";
	} else if ($name != "" && $location != "") {
		$query .= "FILTER ((regex(str(?VenueTitle), '$name', 'i') && regex(str(?Locality), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?Locality), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?VenueTitle), '$location', 'i'))) . 
			";
	}

	// Add the other half of the union.
	$query.= "
			} UNION {
				?Event a ah:Event .
				?Event ah:cidn ?EventId .
				?Event ah:venue ?Venue .
				?Event time:hasBeginning ?Start .
				?Event time:hasEnd ?End .
				?Event ah:production ?Production .
				?Production dc:title ?EventTitle .
				?Production ah:genre ?EventGenre .
				OPTIONAL { ?Production dc:description ?description . }
				?Venue dc:title ?VenueTitle .
				?Venue ah:cidn ?VenueId .
				FILTER ( lang(?VenueTitle) = 'nl' ) .
				?Venue geo:lat ?Lat .
				?Venue geo:long ?Long .
				?Venue ah:venueType ?VenueType .
				?Venue foaf:homepage ?Homepage .
				?Venue ah:locationAddress ?LocAddr .
				?LocAddr vcard:locality ?Locality .
				?LocAddr vcard:postal-code ?PostalCode .
				?LocAddr vcard:street-address ?Address .	
		";

	// Depending on the availability of $name and $location, a combination of filters is added.
	if ($name != "" && $location == "") {
		$query .= "FILTER (regex(str(?EventTitle), '$name', 'i') || regex(str(?VenueTitle), '$name', 'i')) . 
			";
	} else if ($name == "" && $location != "") {
		$query .= "FILTER (regex(str(?VenueTitle), '$location', 'i') || regex(str(?Locality), '$location', 'i')) . 
			";
	} else if ($name != "" && $location != "") {
		$query .= "FILTER ((regex(str(?VenueTitle), '$name', 'i') && regex(str(?Locality), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?Locality), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?VenueTitle), '$location', 'i'))) . 
			";
	}

	$query.= "} } LIMIT 100";

	return $query;
}

// Query for retreiving hotel RDF data from Sesame endpoint.
function makeHotelQuery($location, $name, $startDate, $endDate) {
	global $prefixes;

	// Create query
	$query = $prefixes."

		SELECT ?hotel ?title ?lat ?lng ?city ?id ?city ?address ?hotelRating ?shortDescription ?highRate ?lowRate WHERE {
			?hotel a iwa:Hotel .
			?hotel dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?hotel geo:lat ?lat .
			?hotel geo:long ?lng .
			?hotel geo:city ?city .
			?hotel iwa:id ?id .
			OPTIONAL { ?hotel iwa:Address ?address . } .
			OPTIONAL { ?hotel iwa:rating ?hotelRating . } .
			OPTIONAL { ?hotel dc:description ?shortDescription . } .
			OPTIONAL { ?hotel iwa:highRate ?highRate . } .
			OPTIONAL { ?hotel iwa:lowRate ?lowRate . } .

		";

	// Set filter for location if present.
	if ($location != '') $query .= "FILTER regex(str(?city), str('".str_replace(array("\"", "'"), $replace, $location)."'), 'i') .";

	// Set filter for name if present.
	if ($name != '') $query .= "FILTER regex(str(?title), '$name', 'i') .";

	$query .= "} LIMIT 100";

	return $query;
}

// Query for retreiving hotel RDF data from Sesame endpoint from hotels that are in the same city as $id.
function makeHotelInSameCityQuery($id) {
	global $prefixes;

	// Create query
	$query = $prefixes."

		SELECT ?hotel ?title ?lat ?lng ?city ?id ?city ?address ?hotelRating ?shortDescription ?highRate ?lowRate WHERE {{
			?venue iwa:id \"$id\" .
			?venue iwa:sameCityAs ?hotel .
			?hotel a iwa:Hotel .
			?hotel dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?hotel geo:lat ?lat .
			?hotel geo:long ?lng .
			?hotel geo:city ?city .
			?hotel iwa:id ?id .
			OPTIONAL { ?hotel iwa:Address ?address . } .
			OPTIONAL { ?hotel iwa:rating ?hotelRating . } .
			OPTIONAL { ?hotel dc:description ?shortDescription . } .
			OPTIONAL { ?hotel iwa:highRate ?highRate . } .
			OPTIONAL { ?hotel iwa:lowRate ?lowRate . } .
		} UNION {
			?venue iwa:id \"$id\"^^xsd:string .
			?venue iwa:sameCityAs ?hotel .
			?hotel a iwa:Hotel .
			?hotel dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?hotel geo:lat ?lat .
			?hotel geo:long ?lng .
			?hotel geo:city ?city .
			?hotel iwa:id ?id .
			OPTIONAL { ?hotel iwa:Address ?address . } .
			OPTIONAL { ?hotel iwa:rating ?hotelRating . } .
			OPTIONAL { ?hotel dc:description ?shortDescription . } .
			OPTIONAL { ?hotel iwa:highRate ?highRate . } .
			OPTIONAL { ?hotel iwa:lowRate ?lowRate . } .
		} } LIMIT 100";
		
	return $query;
}

// Query for searching activities by $id at Sesame endpoint.
function makeSearchActivityQuery($id) {
	global $prefixes;

	// Create query
	$query = $prefixes."

		SELECT DISTINCT ?event ?title ?eventTitle ?lat ?lng ?start ?end ?id ?sameAsId ?city WHERE {
			?place iwa:id \"$id\" .
			?event a ah:Event .
			?event ah:venue ?place .
			?event time:hasBeginning ?start .
			?event time:hasEnd ?end .
			?event dc:title ?title .
			?event iwa:id ?id . 
			?place geo:lat ?lat .
			?place geo:long ?lng .
			?place geo:city ?city .
			OPTIONAL { ?place owl:sameAs ?sameAs .
			?sameAs iwa:id ?sameAsId . }
		} LIMIT 100";

	return $query;
}

/* Construct for searching activities taking place at a venue with
homepage $hompage in Arts Holland endpoint. */
function makeArtsHollandVenueConstruct($id, $homepage) {
	global $prefixes;

	// Create dummy string for $homepage if it is empty.
	if ($homepage == "") $homepage = "\"empty\"	";

	/* Create construct with a union. The union is needed as the title of an
	event can come from either the event or the production of the event. */
	$query = $prefixes."

		CONSTRUCT {
			?Event a ah:Event .
			?Event ah:venue ?Venue .
			?Event dc:title ?EventTitle .
			?Event time:hasBeginning ?Start .
			?Event iwa:id ?EventId . 
			?Event time:hasEnd ?End .
			?Event dc:description ?description .
			?Event iwa:eventGenre ?EventGenre .
			?Venue dc:title ?VenueTitle .
			?Venue iwa:id ?VenueId .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
			?Venue ah:venueType ?VenueType .
			?Venue foaf:homepage $homepage .
			?Venue ah:locationAddress ?LocAddr .
			?LocAddr vcard:locality ?Locality .
			?LocAddr vcard:postal-code ?PostalCode .
			?LocAddr vcard:street-address ?Address .	
		} WHERE { {
				?Event a ah:Event .
				?Event ah:cidn ?EventId .
				?Event ah:venue ?Venue .
				?Event time:hasBeginning ?Start .
				?Event time:hasEnd ?End .
				?Event dc:title ?EventTitle .
				?Event ah:production ?Production .
				?Production ah:genre ?EventGenre .
				?Venue dc:title ?VenueTitle .
				?Venue ah:cidn ?VenueId .
				FILTER ( lang(?VenueTitle) = 'nl' ) .
				?Venue geo:lat ?Lat .
				?Venue geo:long ?Long .
				?Venue foaf:homepage $homepage . 
				?Venue ah:locationAddress ?LocAddr .
				?LocAddr vcard:locality ?Locality .
				?LocAddr vcard:postal-code ?PostalCode .
				?LocAddr vcard:street-address ?Address . 
		} UNION {
				?Event a ah:Event .
				?Event ah:cidn ?EventId .
				?Event ah:venue ?Venue .
				?Event time:hasBeginning ?Start .
				?Event time:hasEnd ?End .
				?Event ah:production ?Production .
				?Production ah:genre ?EventGenre .
				?Production dc:title ?EventTitle .
				?Venue dc:title ?VenueTitle .
				?Venue ah:cidn ?VenueId .
				FILTER ( lang(?VenueTitle) = 'nl' ) .
				?Venue geo:lat ?Lat .
				?Venue geo:long ?Long .
				?Venue foaf:homepage $homepage . 
				?Venue ah:locationAddress ?LocAddr .
				?LocAddr vcard:locality ?Locality .
				?LocAddr vcard:postal-code ?PostalCode .
				?LocAddr vcard:street-address ?Address .
		} }";

	return $query;
}

?>
