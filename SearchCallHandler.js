var resultList = [];
var timelineList = [];

function checkValues() {
    if ($("#searchType").val() == "place") {
		if ($("#name").val().length != 0 && $("#location").val().length != 0) {
			return true;
		} else {
			setMessage("Please provide both name and location.", false);
		}
    } else if ($("#searchType").val() == "activity") {
		if ($("#location").val().length != 0) { 
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

function getResults() {
    clearMessage();
    clearResultsList();
    clearMapMarkers();
    
     if (checkValues()) {
		$("body").addClass("loading");
		$.getJSON('SearchCallHandler.php', {
			"searchType": $("#searchType").val(),
			"location": $("#location").val(),
			"name": $("#name").val(),
			"startDate": $("#startdatepicker").val(),
			"endDate": $("#enddatepicker").val(),
			"activityType": $("#activityType").val()
			})
            .success( function(data) {
                parseResponse(data);
                $("body").removeClass("loading");
            })
            .error( function(error) {
                setMessage(error.statusText, true);
                $("body").removeClass("loading");
            });  
    } else {
        clearResultsList();
    }
}

function searchActivities(venueId, venueHomepage) {
    clearMessage();
    //clearResultsList();
    clearMapMarkers();
    
	// Remove search button
	$('div.resultItemButton[id=\"'+venueId+'-searchButton\"]').addClass("hidden");
	
	console.log("search activities for venueId :" + venueId);
	$("body").addClass("loading");
	$.getJSON('SearchActivityHandler.php', {
			"id": venueId,
			"homepage": venueHomepage
		})
		.success( function(data) {
			console.log(data);

			parseVenueActivities(data, venueId);
			$("body").removeClass("loading");
		})
		.error( function(error) {
			setMessage(error.statusText, true);
			$("body").removeClass("loading");
		});
}

// Set the given message. If error is true, set the errorMessage class.
function setMessage(message, error){
	$("#message").text(message);
    $("#message").removeClass("hidden");
    
    if (error) {
		$("#message").addClass("errorMessage");
    } else {
		$("#message").removeClass("errorMessage");
	}

    window.setTimeout(function(){
	    $("#message").addClass("hidden");
    }, 5000);
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

function parseVenueActivities(activities, venueId) {
	var parsedActivities = [];

	if (activities.length > 0) {
		for (var i = 0; i < activities.length; i++) {
			if (parsedActivities.indexOf(activities[i]['id']['value']) === -1) {
				activities[i]['type'] = {"value": "activity"};
				addActivityToResult(activities[i], venueId);
				setActivityMarker(activities[i]);	   
				resultList.push(activities[i]);
				parsedActivities.push(activities[i]['id']['value']);
			}
		}
		
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No activities found.", false);
    }
}

function parseActivities(activities) {
	var venues = [];
	var parsedActivities = [];
	console.log(activities.length);

	if (activities.length > 0) {
		for (var i = 0; i < activities.length; i++) {
			if (venues.indexOf(activities[i]['venueId']['value']) === -1) {
				var venue = [];
				venue['id'] = activities[i]['venueId'];
				venue['type'] = {"value": "venue"};
				venue['title'] = activities[i]['venueTitle'];
				venue['lat'] = activities[i]['lat'];
				venue['lng'] = activities[i]['lng'];

				createResult(venue, false);
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
		
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No activities found.", false);
    }
}

function parseHotels(hotels) {
	var parsedHotels = [];
    if (hotels.length > 0) {
		for (var i = 0; i < hotels.length; i++) {
			if (parsedHotels.indexOf(hotels[i]['id']['value']) === -1) {
				hotels[i]['type'] = {"value": "hotel"};
				setHotelMarker(hotels[i]);	    
				createResult(hotels[i], false);
				resultList.push(hotels[i]);
				parsedHotels.push(hotels[i]['id']['value']);
			}
		}
			
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
    } else {
		setMessage("No hotels found.", false);
    }
}

function parseVenues(venues) {
	var parsedVenues = [];
	
    if (venues.length > 0) {
		for (var i = 0; i < venues.length; i++) {
			if (parsedVenues.indexOf(venues[i]['id']['value']) === -1) {
				venues[i]['type'] = {"value": "venue"};
				setVenueMarker(venues[i]);
				createResult(venues[i], true);
				resultList.push(venues[i]);
				parsedVenues.push(venues[i]['id']['value']);
			}
		}
				
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
    } else {
		setMessage("No venues found.", false);
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

function createResult(result, searchButton) {
	console.log(result);
	var id = result['id']['value'];
    var test = '<div class="result" id=\''+id+'\' onmouseover="highlightMarker(\''+id+'\');" onclick="focusOnMarker(\''+id+'\');"></div>';
	var text = "";
	if (result['city']) text+= result['city']['value'];

	// Activity
	if (result['start']) text+= "<br /><i>Start:</i> " + result['start']['value'];
	if (result['end']) text+= "<br /><i>End:</i> " + result['end']['value'];

	// Hotel
	if (result['lowRate']) text+= "<br /><i>Lowest rate:</i> &#8364; " + Math.round(result['lowRate']['value'] * 100) / 100;
	if (result['highRate']) text+= "<br /><i>Highest rate:</i> &#8364; " + Math.round(result['highRate']['value'] * 100) / 100;
    
    if (searchButton) {
		if (result['homepage']) {
			var searchButton = $('<div class="resultItemButton" id="'+id+'-searchButton" onclick="searchActivities(\''+id+'\', \'<'+result['homepage']['value']+'>\')">');
		} else {
			var searchButton = $('<div class="resultItemButton" id="'+id+'-searchButton" onclick="searchActivities(\''+id+'\', \'\')">');
		}
		var result = $(test).append($('<div class="resultItemTitle">').text(result['title']['value'])
				.append(searchButton));
		result = $(result).append($('<div>').html(text));
	} else {
		var result = $(test).append($('<div class="resultItemTitle">').text(result['title']['value']));
		result = $(result).append($('<div>').html(text));
	}
	
    $(".resultList").append($(result)
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

function createActivityResult(result, searchButton) {
	var id = result['id']['value'];
    var test = '<div class="result" id=\''+id+'\' onmouseover="highlightMarker(\''+id+'\');" onclick="focusOnMarker(\''+id+'\');"></div>';
    
    if (searchButton) {
		var result = $(test).append($('<div class="resultItemTitle">').text(result['title']['value'])
				.append($('<div class="resultItemButton" onclick="searchActivities(\''+id+'\')">')));
		if (result['city']) result = $(result).append($('<div>').text(result['city']['value']));
	} else {
		var result = $(test).append($('<div class="resultItemTitle">').text(result['title']['value']));
		if (result['city']) result = $(result).append($('<div>').text(result['city']['value']));
	}
	
    $(".resultList").append($(result)
	.draggable({
	    cursor: 'move',
	    connectWith: '.timeline',
	    helper: 'clone',
	    opacity: 0.5,
	    zIndex: 10001
	}));
}

function moveItemToTimelineList(id) {
    for (var i = 0; i < resultList.length; i++) {
	if (id == resultList[i]['id']['value']) {
	    timelineList.push(resultList[i]);
	}
    }
	console.log("Adding item, new list:");
    console.log(timelineList);
}

function orderTimelineList() {
	var documentTimelineItems = document.getElementsByClassName("timelineItem");
	var newTimelineList = [];
	
	for (var i = 0; i < documentTimelineItems.length; i++) {
		for (var j = 0; j < timelineList.length; j++) {
			if (documentTimelineItems[j].id == timelineList[i]['id']['value']) {
				newTimelineList.push(timelineList[i]);
			}
		}
	}
	
	console.log("Orderning");
	console.log(timelineList);
	timelineList = newTimelineList;
	console.log(timelineList);
}

function removeItemFromTimeline(id) {
	for (var i = 0; i < timelineList.length; i++) {
		if (id == timelineList[i]['id']['value']) {
			timelineList.splice(i, 1);
		}
	}
	console.log(timelineList);
}


function addActivityToResult(activity, venueId) {
	var text = "<b>"+activity['title']['value']+"</b>";
	if (activity['city']) text+= activity['city']['value'];
	if (activity['start']) text+= "<br /><i>Start:</i> " + activity['start']['value'].replace("Z", "").replace("T", " ");
	if (activity['end']) text+= "<br /><i>End:</i> " + activity['end']['value'].replace("Z", "").replace("T", " ");
	var activityDiv = $('<div class=\"activityInResult\" id="'+activity['id']['value']+'">').html(text);

	$("#"+venueId).append(activityDiv
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
