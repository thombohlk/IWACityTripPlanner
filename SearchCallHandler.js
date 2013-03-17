var resultList = [];
var timelineList = [];

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
    var id;

	if (activities.length > 0) {
		for (var i = 0; i < activities.length; i++) {
			id = setActivityMarker(activities[i]);	    
			createResult(activities[i]['VenueTitle']['value']);
		}
		
		fitMapToMarkers();
		$("#venueListBox").removeClass("hidden");
		
		resultList.push(activities[i]);
	} else {
		setMessage("No activities found.", false);
    }
}

function parseHotels(hotels) {
    var id;

	if (hotels.length > 0) {
		for (var i = 0; i < hotels.length; i++) {
			id = setHotelMarker(hotels[i]);	    
			createResult(hotels[i]['Title']['value']);
		}
		
		fitMapToMarkers();
		$("#venueListBox").removeClass("hidden");
	} else {
		setMessage("No hotels found.", false);
    }
}

function parseVenues(venues) {
    var location, id;
    
	if (venues.length > 0) {
		for (var i = 0; i < venues.length; i++) {
			location = venues[i]['location']
			id = setVenueMarker(venues[i]);	    
			createResult(venues[i]['name'], id);
		
			resultList.push(venues[i]);
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
    resultList = [];
}

// Clear any highlighted results and highlight the result with ID
function highlightResult(id) {
	$("div.result").removeClass("highlightedResult");
	$("#"+id).addClass("highlightedResult");
}

function createResult(name, id) {
    var test = '<div class="result" id=\''+id+'\' onmouseover="highlightMarker(\''+id+'\');" onclick="focusOnMarker(\''+id+'\');"></div>';
    var result = $(test).text(name);
    $(".resultList").append($(result)
	.draggable({
	    cursor: 'move',
	    connectWith: '.timeline',
	    helper: 'clone',
	    opacity: 0.5,
	    zIndex: 10
	}));
}


function moveItemToTimelineList(id) {
	for (var i = 0; i < resultList.length; i++) {
		if (id == resultList[i]['id']) {
			timelineList.push(resultList[i]);
		}
	}
}
