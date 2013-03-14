// Clear everything and get list of venues. If it succeeds parse venues, otherwise give an error.

function checkValues() {
    if ($("#searchType").val() == "place") {
	if ($("#name").val().length != 0 && $("#location").val().length != 0) {
	    return true;
	} else {
	    setMessage("Please provide both name and location.", true);
	}
    } else if ($("#searchType").val() == "activity") {
	if ($("#name").val().length != 0) { 
	    return true;
	} else {
	    setMessage("Please provide an activity name.", true);
	}
    } else if ($("#searchType").val() == "hotel") {
	setMessage("Hotel", false);
	return true;
    } else {
	setMessage("Invalid search type provided.", true);
	return false;
    }
    return false;
}

function getResults() {
    clearMessage();
    clearResultsList();
    clearMapMarkers();
    
     if (checkValues()) {
        $.getJSON('SearchCallHandler.php', { "searchType": $("#searchType").val(), "location": $("#location").val(), "name": $("#name").val() })
            .success( function(data) {
                parseResponse(data); 
            })
            .error( function(error) {
                setMessage(error.statusText, true);
            });  
    } else {
        clearResultsList();
    }
}

// Set the given message. If error is true, set the errorMessage class.
function setMessage(message, error){
    $("#messageSpan").text(message);
    $("#messageSpan").removeClass("hidden");
    
    if (error) {
	$("#messageSpan").addClass("errorMessage");
    }
}

// Clear the message container and set proper classes.
function clearMessage() {
    $("#messageSpan").text("");
    $("#messageSpan").addClass("hidden");  
    $("#messageSpan").removeClass("errorMessage");  
}

function parseResponse(data) {
    if ($("#searchType").val() == "place") {
	parseVenues(data);
    } else if ($("#searchType").val() == "activity") {
	parseActivities(data);
    } else if ($("#searchType").val() == "hotel") {
	parseHotels(data);
    } else {
	clearResultsList();
	setMessage("Unexpected search type error occurred.", true);
    }
}

function parseActivities(activities) {
    var location, id;

	if (activities.length > 0) {
		console.log("bla");
		for (var i = 0; i < activities.length; i++) {
			location = activities[i]['location']
			id = setActivityMarker(activities[i]);	    
			createResult(activities[i]['VenueTitle']['value']);
		}
		
		fitMapToMarkers();
		$("#venueListBox").removeClass("hidden");
		} else {
		setMessage("No activities found.", false);
    }
}

function parseHotels(hotels) {
    // TODO
}

function parseVenues(venues) {
    var location, id;
    
    if (venues.length > 0) {
	for (var i = 0; i < venues.length; i++) {
	    location = venues[i]['location']
	    id = setVenueMarker(venues[i]);	    
	    createResult(venues[i]['name']);
	}
		
	fitMapToMarkers();
	$("#venueListBox").removeClass("hidden");
    } else {
	setMessage("No venues found.", false);
    }
}

// Remove all slots from the venue list and hide the list.
function clearResultsList() {
    $("div.result").remove();
    $("#venueListBox").addClass("hidden");
}

function createResult(name) {
    var result = $('<div class="result"></div>').text(name);
    $(".resultList").append($(result)
	.draggable({
	    cursor: 'pointer',
	    connectWith: '.timeline',
	    helper: 'clone',
	    opacity: 0.5,
	    zIndex: 10
	}));
}
