var directionsDisplay;
var directionsService;
var map;
var currentMarkers = [];
var pinImage;
var pinImageHighlight;
var infowindow;

// Initialize map with proper settings and create standard object such as pinImage.
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
	
    pinImage = new google.maps.MarkerImage("http://maps.google.com/intl/en_us/mapfiles/ms/micons/red.png", null, null, null, new google.maps.Size(32, 32))
    pinImageHighlight = new google.maps.MarkerImage("http://maps.google.com/intl/en_us/mapfiles/ms/micons/blue.png", null, null, null, new google.maps.Size(32, 32));
    infowindow = new google.maps.InfoWindow();
    directionsDisplay.setMap(map);
};

// Set a marker for a venue and return the id.
function setVenueMarker(venue) {
    var positionMarker = new google.maps.LatLng(venue['lat']['value'], venue['lng']['value']);
    var marker = new google.maps.Marker({
	position: positionMarker,
	map: map,
	icon: pinImage,
	title: venue['VenueTitle']['value']
    });

    var id = venue['id']['value'];
    var text = "<b>" + venue['VenueTitle']['value'] + "</b>" + 
	"<br />Address: " + venue['Address']['value'] +
	"<br />Postal Code: " + venue['PostalCode']['value'] +
	"<br />City: " + venue['City']['value'];

    marker.set("id", id);
    makeInfoWindowEvent(map, infowindow, text, marker);

    currentMarkers.push(marker);
    return id;
};

// Set a marker for a activity and return the id.
function setActivityMarker(activity) {
    console.log(activity['Long']['value']);
    console.log(activity['Lat']['value']);
    var positionMarker = new google.maps.LatLng(activity['Lat']['value'], activity['Long']['value']);
    var marker = new google.maps.Marker({
	position: positionMarker,
	map: map,
	icon: pinImage,
	title: activity['VenueTitle']['value']
    });

    //TODO: Add id
    var id = activity[1];
    var text = "<b>" + activity['VenueTitle']['value'] + "</b>";

    marker.set("id", id);
    makeInfoWindowEvent(map, infowindow, text, marker);

    currentMarkers.push(marker);
    return id;
};

// Set a marker for a activity and return the id.
function setHotelMarker(hotel) {
    var positionMarker = new google.maps.LatLng(hotel['lat']['value'], hotel['lng']['value']);
    var marker = new google.maps.Marker({
	position: positionMarker,
	map: map,
	icon: pinImage,
	title: hotel['Title']['value']
    });

    //TODO: Add id
    var id = hotel['id']['value'];
    var text = "<b>" + hotel['Title']['value'] + "</b>";

    marker.set("id", id);
    makeInfoWindowEvent(map, infowindow, text, marker);

    currentMarkers.push(marker);
    return id;
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

function highlightMarker(id) {
    for (var i = 0; i < currentMarkers.length; i++) {
		var idToFind = currentMarkers[i].get("id");
		if (id == idToFind) {
			currentMarkers[i].setIcon(pinImageHighlight);
		} else {
			currentMarkers[i].setIcon(pinImage);
		}
    }
}

// Set proper marker icon for marker with e.data.id, depending on e.data.markerIcon.
function setMarkerIcon(e) {
    for (var i = 0; i < currentMarkers.length; i++) {
		var id = currentMarkers[i].get("id");
		if (id == e.data.id) {
			if (e.data.markerIcon == "highlight") currentMarkers[i].setIcon(pinImageHighlight);
			if (e.data.markerIcon == "regular") currentMarkers[i].setIcon(pinImage);
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
	
	for (var i = 1; i < timelineList.length-1; i++) {
		waypts.push({
			location: new google.maps.LatLng(timelineList[i]['lat']['value'], timelineList[i]['lng']['value']),
			stopover: true
		});
	}
	
	if (timelineList.length >= 2) {
		var start = new google.maps.LatLng(timelineList[0]['lat']['value'], timelineList[0]['lng']['value']);
		var end = new google.maps.LatLng(timelineList[timelineList.length-1]['lat']['value'], timelineList[timelineList.length-1]['lng']['value']);
		var request = {
			origin:start,
			destination:end,
			waypoints: waypts,
			travelMode: google.maps.TravelMode.DRIVING
		};
		directionsService.route(request, function(result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				directionsDisplay.setDirections(result);
			}
		});
	}
}
