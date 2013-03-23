var directionsDisplay;
var directionsService;
var map;
var currentMarkers = [];
var venuePinImage;
var venuePinImageHighlight;
var activityPinImage;
var activityPinImageHighlight;
var hotelPinImage;
var hotelPinImageHighlight;
var infowindow;

// Initialize map with proper settings and create standard object such as venuePinImage.
function initializeMap() {
	directionsService = new google.maps.DirectionsService();
	directionsDisplay = new google.maps.DirectionsRenderer();
    var mapOptions = {
		center: new google.maps.LatLng(52.237892,5.349426),
		zoom: 7,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		panControl: false,
		zoomControl: false,
		mapTypeControl: false,
		scaleControl: false,
		streetViewControl: false,
		overviewMapControl: false
    };

    map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

    google.maps.event.addListener(map, 'tilesloaded', function(){
		document.getElementById("map_canvas").zIndex = -1000;
		document.getElementById("map_canvas").style.position = "static";
    });
	
    venuePinImage = new google.maps.MarkerImage("http://maps.google.com/mapfiles/markerV.png", null, null, null, new google.maps.Size(20, 34))
    venuePinImageHighlight = new google.maps.MarkerImage("http://maps.google.com/mapfiles/marker_yellowV.png", null, null, null, new google.maps.Size(20, 34));
    activityPinImage = new google.maps.MarkerImage("http://maps.google.com/mapfiles/markerA.png", null, null, null, new google.maps.Size(20, 34))
    activityPinImageHighlight = new google.maps.MarkerImage("http://maps.google.com/mapfiles/marker_yellowA.png", null, null, null, new google.maps.Size(20, 34));
    hotelPinImage = new google.maps.MarkerImage("http://maps.google.com/mapfiles/ms/micons/lodging.png", null, null, null, new google.maps.Size(32, 32))
    hotelPinImageHighlight = new google.maps.MarkerImage("http://maps.google.com/mapfiles/arrow.png", null, null, null, new google.maps.Size(39, 34));
    infowindow = new google.maps.InfoWindow();
    directionsDisplay.setMap(map);
};

// Set a marker for a venue and return the id.
function setVenueMarker(venue) {
    var positionMarker = new google.maps.LatLng(venue['lat']['value'], venue['lng']['value']);
    var marker = new google.maps.Marker({
		position: positionMarker,
		map: map,
		icon: venuePinImage,
		title: venue['title']['value']
    });

    var id = venue['id']['value'];
    var text = "<b>" + venue['title']['value'] + "</b>" + 
	"<br />Address: " + venue['address']['value'] +
	"<br />Postal Code: " + venue['postalCode']['value'] +
	"<br />City: " + venue['city']['value'];

    marker.set("id", id);
    marker.set("type", "venue");
    makeInfoWindowEvent(map, infowindow, text, marker);

    currentMarkers.push(marker);
};

// Set a marker for a activity and return the id.
function setActivityMarker(activity) {
    var positionMarker = new google.maps.LatLng(activity['lat']['value'], activity['lng']['value']);
    var marker = new google.maps.Marker({
		position: positionMarker,
		map: map,
		icon: activityPinImage,
		title: activity['title']['value']
    });

    var id = activity['id']['value'];
    var text = "<b>" + activity['title']['value'] + "</b>";
	if (activity['start']) text+= "<br />Start: " + activity['start']['value'].replace("Z", "").replace("T", " ");
	if (activity['end']) text+= "<br />End: " + activity['end']['value'].replace("Z", "").replace("T", " ");
	if (activity['description']) text+= "<br />" + activity['description']['value'];

    marker.set("id", id);
    marker.set("type", "activity");
    makeInfoWindowEvent(map, infowindow, text, marker);

    currentMarkers.push(marker);
};

// Set a marker for a activity and return the id.
function setHotelMarker(hotel) {
    var positionMarker = new google.maps.LatLng(hotel['lat']['value'], hotel['lng']['value']);
    var marker = new google.maps.Marker({
		position: positionMarker,
		map: map,
		icon: hotelPinImage,
		title: hotel['title']['value']
    });

    //TODO: Add id
    var id = hotel['id']['value'];
    var text = "<b>" + hotel['title']['value'] + "</b>";
	if (hotel['lowRate']) text+= "<br /><i>Lowest rate:</i> &#8364; " + Math.round(hotel['lowRate']['value'] * 100) / 100;
	if (hotel['highRate']) text+= "<br /><i>Highest rate:</i> &#8364; " + Math.round(hotel['highRate']['value'] * 100) / 100;
	if (hotel['hotelRating']) text+= "<br />" + hotel['hotelRating']['value']+" stars out of 5";
	if (hotel['shortDescription']) text+= "<br />" + hotel['shortDescription']['value'];

    marker.set("id", id);
    marker.set("type", "hotel");
    makeInfoWindowEvent(map, infowindow, text, marker);

    currentMarkers.push(marker);
};

// Create an on click window event to show infowindow with content for marker.
function makeInfoWindowEvent(map, infowindow, content, marker) {
    google.maps.event.addListener(marker, 'click', function() {
		infowindow.setContent(content);
		infowindow.open(map, marker); 
		highlightMarker(marker.get("id"));
		highlightResult(marker.get("id"));
    });
};

// Move to location in e.data.lat/lng on map.
function goToLocation(e) {
    var position = new google.maps.LatLng(e.data.lat, e.data.lng);
    map.panTo(position);
    if (e.data.zoom) map.setZoom(18);
};

function findIcon(markerType, iconType) {
    if (markerType == "venue") {
		if (iconType == "highlight") {
			return venuePinImageHighlight;
		} else if (iconType == "normal") {
			return venuePinImage;
		}
    } else if (markerType == "activity") {
		if (iconType == "highlight") {
			return activityPinImageHighlight;
		} else if (iconType == "normal") {
			return activityPinImage;
		}
    } else if (markerType == "hotel") {
		if (iconType == "highlight") {
			return hotelPinImageHighlight;
		} else if (iconType == "normal") {
			return hotelPinImage;
		}
    } else {
		setMessage("Unexpected pin image type error", true);
    }
}

function highlightMarker(id) {
    for (var i = 0; i < currentMarkers.length; i++) {
		var idToFind = currentMarkers[i].get("id");
		if (id == idToFind) {
		    var highlightIcon = findIcon(currentMarkers[i].get("type"), "highlight");
		    currentMarkers[i].setIcon(highlightIcon);
		} else {
		    var normalIcon = findIcon(currentMarkers[i].get("type"), "normal");
		    currentMarkers[i].setIcon(normalIcon);
		}
    }
}

// Set proper marker icon for marker with e.data.id, depending on e.data.markerIcon.
function setMarkerIcon(e) {
    for (var i = 0; i < currentMarkers.length; i++) {
		var id = currentMarkers[i].get("id");
		if (id == e.data.id) {
			if (e.data.markerIcon == "highlight") currentMarkers[i].setIcon(venuePinImageHighlight);
			if (e.data.markerIcon == "regular") currentMarkers[i].setIcon(venuePinImage);
		}
    }
};

// Fit map to show all markers currently presented.
function fitMapToMarkers() {
    var bounds = new google.maps.LatLngBounds ();
    for (var i = 0; i < currentMarkers.length; i++) {
	bounds.extend(currentMarkers[i].getPosition());
    }
    map.fitBounds(bounds);
};

// Focus on a marker on the map.
function focusOnMarker(id) {
    for (var i = 0; i < currentMarkers.length; i++) {
		var markerID = currentMarkers[i].get("id");
		if (id == markerID) {
			map.setCenter(currentMarkers[i].getPosition());
			infowindow.open(map, currentMarkers[i]);
		}
    }
}

// Clear all markers on the map.
function clearMapMarkers(){
    for (var i = 0; i < currentMarkers.length; i++) {
		currentMarkers[i].setMap(null);
	}
    currentMarkers = [];
};

function calcRoute() {
	var waypts = [];
	var docTimelineItems = document.getElementsByClassName("timelineItem");

	console.log("Calculating route.");
	for (var i = 0; i < docTimelineItems.length; i++) {
	    for (var j = 0; j < timelineList.length; j++) {
		if (docTimelineItems[i].id == timelineList[j]['id']['value']) {
		    if (i === 0) {
			var start = new google.maps.LatLng(timelineList[j]['lat']['value'], timelineList[j]['lng']['value']);
		    } else if (i < docTimelineItems.length-1) {
			waypts.push({
			    location: new google.maps.LatLng(timelineList[j]['lat']['value'], timelineList[j]['lng']['value']),
			    stopover: true
			});
		    } else {
			var end = new google.maps.LatLng(timelineList[j]['lat']['value'], timelineList[j]['lng']['value']);
		    }
		}
	    }
	}
	
	if (timelineList.length >= 2) {
		console.log("Timeline list length: "+ timelineList.length);
		var transitMode = "google.maps.TravelMode."+$("#timelineTravelMode").val();

		var request = {
			origin:start,
			destination:end,
			waypoints: waypts,
			travelMode: eval("google.maps.TravelMode."+$("#timelineTravelMode").val())
		};
		directionsService.route(request, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				var totalDist = 0;
				var totalDur = 0;

				for (var i = 0; i < result['routes'][0]['legs'].length; i++) {
					totalDist+= result['routes'][0]['legs'][i]['distance']['value'];
					totalDur+= result['routes'][0]['legs'][i]['duration']['value'];
				}
				$("#timelineDistance").text(Math.ceil(totalDist/1000) + " km");
				$("#timelineDuration").text(Math.floor(totalDur/3600) + " hours " + Math.ceil(totalDur%3600 / 60) + " min");

				directionsDisplay.setDirections(result);
			} else {
				setMessage("Could not find a route for this combination of places and travel mode.", false);
			}
		});
	}
}
