<?php
/*
print "<html><head></head><body>";

print "MAKEARTSHOLLANDQUERY(museon, 'den haag', nopref)<br><br>";
print "<pre>";
makeArtsHollandQuery("museon", "den haag", "NoPref");
print "</pre>";
print "<br><br><hr><br><br>";

print "MAKEARTSHOLLANDQUERY('', 'den haag', nopref)<br><br>";
print "<pre>";
makeArtsHollandQuery("", "den haag", "NoPref");
print "</pre>";
print "<br><br><hr><br><br>";

print "</body></html>";
*/
// Foursquare venue query
function makeVenueQuery($name, $location) {
    // Create query
    $query = "
	PREFIX iwa: <http://example.org/iwa/>
	PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
	PREFIX dc: <http://purl.org/dc/terms/>
	PREFIX fs: <https://api.foursquare.com/v2/venues/>

	SELECT DISTINCT ?venue ?id ?title ?lat ?lng ?address ?postalCode ?city WHERE {";

    	$query .= "
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

function makeArtsHollandQuery($name, $location, $activityType, $startDate, $endDate) {
	// Create query
	$query = "
		PREFIX ah: <http://purl.org/artsholland/1.0/>
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
			OPTIONAL { ?venue owl:sameAs ?sameAs .
			?sameAs iwa:id ?sameAsVenueId .
			?venue ah:locationAddress ?locAdd . }
			?locAdd vcard:locality ?locatie . 
		";

		if ($name != "" && $location == "") {
			$query .= "FILTER (regex(str(?title), '$name', 'i') || regex(str(?venueTitle), '$name', 'i')) . 
				";
		} else if ($name == "" && $location != "") {
			$query .= "FILTER (regex(str(?venueTitle), '$location', 'i') || regex(str(?locatie), '$location', 'i')) . 
				";
		} else if ($name != "" && $location != "") {
			$query .= "FILTER ((regex(str(?venueTitle), '$name', 'i') && regex(str(?locatie), '$location', 'i')) ||
								(regex(str(?title), '$name', 'i') && regex(str(?locatie), '$location', 'i')) ||
								(regex(str(?title), '$name', 'i') && regex(str(?venueTitle), '$location', 'i'))) . 
				";
		}

		if ($startDate != "") {
			$query .= "FILTER (xsd:dateTime(?start) >= \"$startDate\"^^xsd:dateTime) . 
				";
		}
		if ($endDate != "") {
			$query .= "FILTER (xsd:dateTime(?end) <= \"$endDate\"^^xsd:dateTime) . 
				";
		}

		if ($activityType != "NoPref") {
			$query .= "?event iwa:eventType ah:VenueType".$activityType." .
				";
		}

	$query .= "} LIMIT 100";
	return $query;
//	print $query;
}

function makeArtsHollandConstruct($name, $location, $activityType) {
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
			?Venue dc:title ?VenueTitle .
			?Venue iwa:id ?VenueId .
			?Venue geo:lat ?Lat .
			?Venue geo:long ?Long .
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

	if ($name != "" && $location == "") {
		$query .= "FILTER (regex(str(?EventTitle), '$name', 'i') || regex(str(?VenueTitle), '$name', 'i')) . 
			";
	} else if ($name == "" && $location != "") {
		$query .= "FILTER (regex(str(?VenueTitle), '$location', 'i') || regex(str(?locatie), '$location', 'i')) . 
			";
	} else if ($name != "" && $location != "") {
		$query .= "FILTER ((regex(str(?VenueTitle), '$name', 'i') && regex(str(?locatie), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?locatie), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?VenueTitle), '$location', 'i'))) . 
			";
	}

	$query.= "
			} UNION {
				?Event a ah:Event .
				?Event ah:cidn ?EventId .
				?Event ah:venue ?Venue .
				?Event time:hasBeginning ?Start .
				?Event time:hasEnd ?End .
				?Event ah:production ?Production .
				?Production dc:title ?EventTitle .
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

	if ($name != "" && $location == "") {
		$query .= "FILTER (regex(str(?EventTitle), '$name', 'i') || regex(str(?VenueTitle), '$name', 'i')) . 
			";
	} else if ($name == "" && $location != "") {
		$query .= "FILTER (regex(str(?VenueTitle), '$location', 'i') || regex(str(?locatie), '$location', 'i')) . 
			";
	} else if ($name != "" && $location != "") {
		$query .= "FILTER ((regex(str(?VenueTitle), '$name', 'i') && regex(str(?locatie), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?locatie), '$location', 'i')) ||
							(regex(str(?EventTitle), '$name', 'i') && regex(str(?VenueTitle), '$location', 'i'))) . 
			";
	}

	$query.= "} } LIMIT 100";

	return $query;
}

function makeHotelQuery($location, $name, $startDate, $endDate) {
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

		SELECT ?hotel ?title ?lat ?lng ?city ?id ?city ?address ?hotelRating ?shortDescription ?highRate ?lowRate WHERE {
			?hotel a iwa:Hotel .
			?hotel dc:title ?title .
			FILTER ( lang(?title) = 'nl' ) .
			?hotel geo:lat ?lat .
			?hotel geo:long ?lng .
			?hotel geo:city ?city .
			?hotel iwa:id ?id .
			OPTIONAL { ?hotel geo:city ?city . } .
			OPTIONAL { ?hotel iwa:Address ?address . } .
			OPTIONAL { ?hotel iwa:rating ?hotelRating . } .
			OPTIONAL { ?hotel dc:description ?shortDescription . } .
			OPTIONAL { ?hotel iwa:highRate ?highRate . } .
			OPTIONAL { ?hotel iwa:lowRate ?lowRate . } .

		";

	if ($location != '') { 
		$query .= "FILTER regex(str(?city), '$location', 'i') .";
	}

	if ($name != '') { 
		$query .= "FILTER regex(str(?title), '$name', 'i') .";
	}

	/*if ($startDate != '') {

	}
FILTER(xsd:dateTime(?birth) >= "1984-12-12T00:00:00Z"^^xsd:dateTime &&
        xsd:dateTime(?birth) <= "1984-12-12T23:59:59Z"^^xsd:dateTime) .*/

	$query .= "} LIMIT 1000";

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

		SELECT DISTINCT ?event ?title ?eventTitle ?lat ?lng ?start ?end ?id ?sameAsId WHERE {
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
			?place owl:sameAs ?sameAs .
			?sameAs iwa:id ?sameAsId .
		} LIMIT 100";
		
	//print $query; exit();
	return $query;
}

?>
