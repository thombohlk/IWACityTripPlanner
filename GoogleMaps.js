var map;
var currentMarkers = [];

var pinImage;
var pinImageHeighlight;
var infowindow;

// Initialize map with proper settings and create standard object such as pinImage.
function initializeMap() {
	var mapOptions = {
		center: new google.maps.LatLng(0, 0),
		zoom: 2,
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
	pinImageHeighlight = new google.maps.MarkerImage("http://maps.google.com/intl/en_us/mapfiles/ms/micons/blue.png", null, null, null, new google.maps.Size(48, 48));
	infowindow = new google.maps.InfoWindow();
};

// Set a marker for a venue and return the id.
function setVenueMarker(venue) {
	var positionMarker = new google.maps.LatLng(venue['location']['lat'], venue['location']['lng']);

	var marker = new google.maps.Marker({
		position: positionMarker,
		map: map,
		icon: pinImage,
		title: venue['name']
	});
	
	var id = venue['id'];
	var text = "<b>" + venue['name'] + "</b>" + 
		"<br />Address: " + venue['location']['address'] +
		"<br />Postal Code: " + venue['location']['postalCode'] +
		"<br />City: " + venue['location']['city'];
	
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
  });
};

// Move to location in e.data.lat/lng on map.
function goToLocation(e) {
	var position = new google.maps.LatLng(e.data.lat, e.data.lng);
	
	map.panTo(position);
	if (e.data.zoom) map.setZoom(18);
};

// Set proper marker icon for marker with e.data.id, depending on e.data.markerIcon.
function setMarkerIcon(e) {
        
	for (var i = 0; i < currentMarkers.length; i++) {
		var id = currentMarkers[i].get("id");
		if (id == e.data.id) {
			if (e.data.markerIcon == "heighlight") currentMarkers[i].setIcon(pinImageHeighlight);
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

// Clear all markers on the map.
function clearMapMarkers(){
	for (var i = 0; i < currentMarkers.length; i++) {
		currentMarkers[i].setMap(null);
	}
	currentMarkers = [];
};