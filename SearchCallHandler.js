
// Define arrays to store items that are placed in the result list and on the timeline.
var resultList = [];
var timelineList = [];

// Calls the SeachCallHandler.php to retreive the results for a search request.
function getResults() {    
	if (checkValues()) {
		// Set loading animation
		$("body").addClass("loading");

		// Do JSON call.
		$.getJSON('SearchCallHandler.php', {
			"searchType": $("#searchType").val(),
			"location": $("#location").val(),
			"name": $("#name").val(),
			"startDate": $("#startdatepicker").val(),
			"endDate": $("#enddatepicker").val(),
			"activityType": $("#activityType").val()
		})
		.success( function(data) {
				// On success parse response data and remove loading animation.
				parseResponse(data);
				$("body").removeClass("loading");
			})
		.error( function(error) {
				// On error show error message and remove loading animation.
				setMessage("An error has occured. Error data: "+error.statusText, true);
				$("body").removeClass("loading");
			});  
	}
}

// Check to ensure the right values are filled in for each type of request.
function checkValues() {
	if ($("#searchType").val() == "place") {
		if ($("#name").val().length != 0 && $("#location").val().length != 0) {
			return true;
		} else {
			setMessage("Please provide both name and location.", false);
		}
	} else if ($("#searchType").val() == "activity") {
		if ($("#location").val().length != 0 || $("#name").val().length != 0) { 
			return true;
		} else {
			setMessage("Please provide an activity name.", false);
		}
	} else if ($("#searchType").val() == "hotel") {
		return true;
	} else {	
		setMessage("Invalid search type provided.", false);
		return false;
	}
	return false;
}

// Search all activities for a venue with venueId and venueHomepage.
function searchActivities(venueId, venueHomepage) {    
	// Remove search button
	$('div.searchButton[id=\"'+venueId+'-searchVenueButton\"]').addClass("hidden");
	
	// Set loading animation
	$("body").addClass("loading");

	// Do JSON call
	$.getJSON('SearchActivityHandler.php', {
			"id": venueId,
			"homepage": venueHomepage
		})
		.success( function(data) {
			// On success parse response data and remove loading animation.
			parseVenueActivities(data, venueId);
			$("body").removeClass("loading");
		})
		.error( function(error) {
			// On error show error message and remove loading animation.
				setMessage("An error has occured. Error data: "+error.statusText, true);
			$("body").removeClass("loading");
		});
}

// Search all hotels for in the same city as a venue with venueId and venueLocation.
function searchHotels(venueId, venueLocation) {
	// Remove search button
	$('div.searchButton[id=\"'+venueId+'-searchHotelButton\"]').addClass("hidden");

	// Set loading animation
	$("body").addClass("loading");

	// Do JSON call
	$.getJSON('SearchHotelHandler.php', {
			"id": venueId,
			"location": venueLocation
		})
		.success( function(data) {
			// On success parse response data and remove loading animation.
			parseHotels(data);
			$("body").removeClass("loading");
		})
		.error( function(error) {
			// On error show error message and remove loading animation.
			setMessage("An error has occured. Error data: "+error.statusText, true);
			$("body").removeClass("loading");
		});
}

// Calls the correct function to parse the response data according to the search type.
function parseResponse(data) {
	var searchType = $("#searchType").val();

    if (searchType == "place") {
		parseVenues(data);
    } else if (searchType == "activity") {
		parseActivities(data);
    } else if (searchType == "hotel") {
		parseHotels(data);
    } else {
		setMessage("Unexpected search type error occurred.", true);
    }
}

// Parse venues retrieved from a search request of type 'Place'.
function parseVenues(venues) {
	var parsedVenues = [];

	// If there are any venues clear previous results and parse them, otherwise set a message no venues were found.
	if (venues.length > 0) {
		clearResultsList();
		clearMapMarkers();

		// Check each found venue if it has not already been parsed. If not, set a marker and create a result item.
		for (var i = 0; i < venues.length; i++) {
			if (parsedVenues.indexOf(venues[i]['id']['value']) === -1) {
				venues[i]['type'] = {"value": "venue"};
				setVenueMarker(venues[i]);
				createResult(venues[i], true, true);
				resultList.push(venues[i]);
				parsedVenues.push(venues[i]['id']['value']);
			}
		}
		
		// Set the map to fit the markers created for the venues and show the resultListBox.
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No venues found.", false);
	}
}

// Parse activities retrieved from a search request of type 'Activity'.
function parseActivities(activities) {
	var venues = [];
	var parsedActivities = [];

	// If there are any activities clear previous results and parse them, otherwise set a message no activities were found.
	if (activities.length > 0) {
		clearResultsList();
		clearMapMarkers();

		/* Check each found activities if its venue has not already been parsed. If not, create a venue list item and check 
			if the activity itself has not been parsed. If that is also not the case, create a marker and list item for
			the activity. */
		for (var i = 0; i < activities.length; i++) {
			if (venues.indexOf(activities[i]['venueId']['value']) === -1) {
				var venue = [];
				venue['id'] = activities[i]['venueId'];
				venue['type'] = {"value": "venue"};
				venue['title'] = activities[i]['venueTitle'];
				venue['lat'] = activities[i]['lat'];
				venue['lng'] = activities[i]['lng'];
				venue['city'] = activities[i]['location'];

				createResult(venue, false, true);
				venues.push(activities[i]['venueId']['value']);
				if (activities[i]['sameAsVenueId']) {
					venues.push(activities[i]['sameAsVenueId']['value']);
				}
				resultList.push(venue);
			}

			if (parsedActivities.indexOf(activities[i]['id']['value']) === -1) {
				activities[i]['type'] = {"value": "activity"};
				addActivityToResult(activities[i], activities[i]['venueId']['value']);
				setActivityMarker(activities[i]);
				resultList.push(activities[i]);
				parsedActivities.push(activities[i]['id']['value']);
			}
		}

		// Set the map to fit the markers created for the activities and show the resultListBox.
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No activities found.", false);
	}
}

// Parse activities retrieved from a search request for activties at venue with venueId.
function parseVenueActivities(activities, venueId) {
	var parsedActivities = [];

	// If any activities have been found, parse them. Other wise set a message no activities have been found.
	if (activities.length > 0) {
		/* For each activity check if it has not been parse already. If not, create a marker and add a list 
			item to the list item of the venue with venueId. */
		for (var i = 0; i < activities.length; i++) {
			if (parsedActivities.indexOf(activities[i]['id']['value']) === -1) {
				activities[i]['type'] = {"value": "activity"};
				addActivityToResult(activities[i], venueId);
				setActivityMarker(activities[i]);	   
				resultList.push(activities[i]);
				parsedActivities.push(activities[i]['id']['value']);
			}
		}

		// Set the map to fit the markers created for the activities and show the resultListBox.
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No activities found.", false);
	}
}

// Parse hotels retrieved from a search request of type 'Hotel'.
function parseHotels(hotels) {
	var parsedHotels = [];

	// If there are any hotels clear previous results and parse them, otherwise set a message no hotels were found.
	if (hotels.length > 0) {
		clearResultsList();
		clearMapMarkers();

		// Check each found hotels if it has not already been parsed. If not, set a marker and create a result item.
		for (var i = 0; i < hotels.length; i++) {
			if (parsedHotels.indexOf(hotels[i]['id']['value']) === -1) {
				hotels[i]['type'] = {"value": "hotel"};
				setHotelMarker(hotels[i]);	    
				createResult(hotels[i], false);
				resultList.push(hotels[i]);
				parsedHotels.push(hotels[i]['id']['value']);
			}
		}
		
		// Set the map to fit the markers created for the hotels and show the resultListBox.
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No hotels found.", false);
	}
}

// Remove all slots from the venue list and hide the list.
function clearResultsList() {
    $("div.result").remove();
    $("#resultListBox").addClass("hidden");
    resultList = [];
}

// Clear any highlighted results and highlight the result with ID
function highlightResult(id) {
	$("div.result").removeClass("highlightedResult");
	$("div.activityInResult").removeClass("highlightedResult");
	$("#"+id).addClass("highlightedResult");
}

// Create an item for the result list, with the content depending on the type of item.
function createResult(result, setActivitySearchButton, setHotelSearchButton) {
	var id = result['id']['value'];
	var text = "";
	
	// Create a div for the resultItem.
	var resultItem = '<div class="result" id=\''+id+'\' onmouseover="highlightMarker(\''+id+'\');" onclick="focusOnMarker(\''+id+'\'); highlightResult(\''+id+'\');"></div>';

	// Set text for any item
	if (result['city']) text+= result['city']['value'];

	// Set text for activity
	if (result['start']) text+= "<br /><i>Start:</i> " + result['start']['value'];
	if (result['end']) text+= "<br /><i>End:</i> " + result['end']['value'];

	// Set text for hotel
	if (result['lowRate']) text+= "<br /><i>Lowest rate:</i> &#8364; " + Math.round(result['lowRate']['value'] * 100) / 100;
	if (result['highRate']) text+= "<br /><i>Highest rate:</i> &#8364; " + Math.round(result['highRate']['value'] * 100) / 100;
	if (result['hotelRating']) text+= "<br />" + result['hotelRating']['value']+" stars out of 5";
    
	// Add a title, buttons and the text as html to the resultItem.
	resultItem = $(resultItem).append($('<div class="resultItemTitle">').text(result['title']['value']));
	addButtonsToItem(resultItem, result, setActivitySearchButton, setHotelSearchButton);
	resultItem = $(resultItem).append($('<div>').html(text));
	
	// Add the resultItem to the resultList as a draggable item.
    $(".resultList").append($(resultItem)
	.draggable({
	    cursor: 'move',
		appendTo: 'body',
		containment: 'window',
		scroll: false,
	    connectWith: '.timeline',
	    helper: 'clone',
	    opacity: 0.5,
	    zIndex: 10001
	}));
}

// Depending of the settings and the availeble data, adds buttons to resultItem.
function addButtonsToItem(resultItem, result, setActivitySearchButton, setHotelSearchButton) {
	var id = result['id']['value'];
	if (setActivitySearchButton && result['homepage']) 
		$(resultItem).append($('<div class="searchButton searchActivityButton" title="Search for activities in this venue" id="'+id+'-searchVenueButton" onclick="searchActivities(\''+id+'\', \'<'+result['homepage']['value']+'>\')">'));
	if (setHotelSearchButton && result['city'])
		$(resultItem).append($('<div class="searchButton searchHotelButton" title="Search for hotels in the same city" id="'+id+'-searchHotelButton" onclick="searchHotels(\''+id+'\', \''+result['city']['value']+'\')">'));
}

// Creates an item for a venue item in the result list.
function addActivityToResult(activity, venueId) {
	// Create activityResult with content text.
	var text = "<b>"+activity['title']['value']+"</b>";
	if (activity['city']) text+= activity['city']['value'];
	if (activity['start']) text+= "<br /><i>Start:</i> " + activity['start']['value'].replace("Z", "").replace("T", " ");
	if (activity['end']) text+= "<br /><i>End:</i> " + activity['end']['value'].replace("Z", "").replace("T", " ");
	var activityResult = $('<div class=\"activityInResult\" id="'+activity['id']['value']+'" onmouseover="highlightMarker(\''+activity['id']['value']+'\');">').html(text);

	/* Set onclick function. Ensure the onclick does not bubble up to the venue div as this would 
		overwrite the effect of the onclick on the activity. */
	$(activityResult).click(function(e) {
		focusOnMarker(activity['id']['value']); highlightResult(activity['id']['value']);
		if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	});

	// Add the activityResult to the venue result item as a draggable item.
	$("#"+venueId).append(activityResult
	.draggable({
	    cursor: 'move',
		appendTo: 'body',
		containment: 'window',
		scroll: false,
	    connectWith: '.timeline',
	    helper: 'clone',
	    opacity: 0.5,
	    zIndex: 10001
	}));
}

// When a items is dragged to the timeline, add the item to the array of timeline items.
function moveItemToTimelineList(id) {
    for (var i = 0; i < resultList.length; i++) {
		if (id == resultList[i]['id']['value']) {
			timelineList.push(resultList[i]);
		}
    }
}

// When a items is removed from the timeline, remove the item from the array of timeline items.
function removeItemFromTimeline(id) {
	for (var i = 0; i < timelineList.length; i++) {
		if (id == timelineList[i]['id']['value']) {
			timelineList.splice(i, 1);
		}
	}
}

// Display the given message for 5 seconds. If error is true, set the errorMessage class.
function setMessage(message, error){
	$("#message").text(message);
    $("#message").removeClass("hidden");
    
    if (error) $("#message").addClass("errorMessage");

    window.setTimeout(function(){
	    $("#message").addClass("hidden");
		$("#message").removeClass("errorMessage");
    }, 5000);
}

// Calls SesameCallHandler to reset the Sesame repository.
function resetRepository() {
	$("body").addClass("loading");
	$.getJSON('SesameCallHandler.php', {
			"call": "reset"
			})
            .success( function(data) {
                $("body").removeClass("loading");
                setMessage("Starting with a clean sheet.", false);
            })
            .error( function(error) {
                $("body").removeClass("loading");
                setMessage("An error has occured while reseting the repository. Error data: "+error.statusText, true);
            });  
}
