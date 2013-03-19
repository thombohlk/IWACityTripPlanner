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
		if ($("#location").val().length != 0) { 
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

function searchActivities(id) {
    clearMessage();
    //clearResultsList();
    clearMapMarkers();
    
	$("body").addClass("loading");
	$.getJSON('SearchActivityHandler.php', {
			"id": id
		})
		.success( function(data) {
			parseActivities(data);
			$("body").removeClass("loading");
		})
		.error( function(error) {
			setMessage(error.statusText, true);
			$("body").removeClass("loading");
		});
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
	if (activities.length > 0) {
		for (var i = 0; i < activities.length; i++) {
			setActivityMarker(activities[i]);	    
			createResult(activities[i], false);
			resultList.push(activities[i]);
		}
		
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
	} else {
		setMessage("No activities found.", false);
    }
}

function parseHotels(hotels) {
    if (hotels.length > 0) {
		for (var i = 0; i < hotels.length; i++) {
			setHotelMarker(hotels[i]);	    
			createResult(hotels[i], false);
			resultList.push(hotels[i]);
		}
			
		fitMapToMarkers();
		$("#resultListBox").removeClass("hidden");
    } else {
		setMessage("No hotels found.", false);
    }
}

function parseVenues(venues) {
    if (venues.length > 0) {
		for (var i = 0; i < venues.length; i++) {
			setVenueMarker(venues[i]);	    
			createResult(venues[i], true);
			resultList.push(venues[i]);
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
	$("#"+id).addClass("highlightedResult");
}

function createResult(result, searchButton) {
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


function addActivityToResult(activity, id) {
	$("#"+id).append($('<div class=\"activityInResult\" id="'+id+'">')
	.draggable({
	    cursor: 'move',
	    connectWith: '.timeline',
	    helper: 'clone',
	    opacity: 0.5,
	    zIndex: 10001
	}));
}
