
// Show or hide input fields according to the value set for search type.
function updateInputFields() {
	var searchType = $( "#searchType" ).val();

	if (searchType == "place" ) {
		$( ".hotelClass").addClass("hidden");
		$( ".activityClass").addClass("hidden");
		$( ".placeClass").removeClass("hidden");
	} else if (searchType == "activity") {
		$( ".hotelClass").addClass("hidden");
		$( ".placeClass").addClass("hidden");
		$( ".activityClass").removeClass("hidden");
	} else if (searchType == "hotel") {
		$( ".activityClass").addClass("hidden");
		$( ".placeClass").addClass("hidden");
		$( ".hotelClass").removeClass("hidden");
	} else {
		console.log("Error in search type! HelperFunctions.js, line 17");
	}
}

// Add datepickers to the start date and end date field.
$(function() { 
	$( "#startdatepicker" ).datepicker({
		dateFormat: 'dd-mm-yy',
		changeMonth: true,
		changeYear: true,
		minDate: 0,
		onSelect: function(selected) {
			$("#enddatepicker").datepicker("option","minDate", selected)
		}

	},
	$.datepicker.regional['nl']);
	$("#startdatepicker").datepicker("setDate",new Date());
	});
$(function() {
	$( "#enddatepicker" ).datepicker({
		dateFormat: 'dd-mm-yy',
		changeMonth: true,
		changeYear: true,
		minDate: 1,
		onSelect: function(selected) {
			$("#startdatepicker").datepicker("option","maxDate", selected)
		}
	},
	$.datepicker.regional['nl']);
	});

// Adds all jQuery functions to setup the draggabillity and sortabillity of result items.
$(function () {
	$('.result').draggable({
		appendTo: 'body',
		containment: 'window',
		scroll: false,
		cursor: 'move',
		connectWith: '.timeline',
		helper: 'clone',
		opacity: 0.5,
		zIndex: 10001
	});
		
	$('.timeline').sortable({
		appendTo: 'body',
		containment: 'window',
		connectWith: '.timeline, #trashcan',
		cursor: 'move',
		stop: function(event, ui) {
			calcRoute();
		}
	}).droppable({
		accept: '.result, .activityInResult',
		activeClass: 'highlight',
		drop: function(event, ui) {
			var id = $(ui.draggable).attr("id");
			var text = id;
			for (var i = 0; i < resultList.length; i++) {
				if (resultList[i]['id'] && id == resultList[i]['id']['value']) {
					var result = resultList[i];
					var listItem;

					text = '<b>' + result['title']['value'] + '</b>';
					if (result['city']) text+= '<br />' + result['city']['value'];
					if (result['start']) text+= '<br />' + result['start']['value'].replace("Z", "").replace("T", " ");
					if (result['end']) text+= '<br />' + result['end']['value'].replace("Z", "").replace("T", " ");

					// Hotel
					if (result['lowRate']) text+= "<br /><i>Lowest rate:</i> &#8364; " + Math.round(result['lowRate']['value'] * 100) / 100;
					if (result['highRate']) text+= "<br /><i>Highest rate:</i> &#8364; " + Math.round(result['highRate']['value'] * 100) / 100;
					if (result['hotelRating']) text+= "<br />" + result['hotelRating']['value']+" stars out of 5";

					listItem = $('<div class="timelineItem" >').html(text);

					if (result['type']['value'] == 'hotel') {
						listItem.addClass("hotelItem");
					} else if (result['type']['value'] == 'venue') {
						addButtonsToItem(listItem, result, false, true);
						listItem.addClass("venueItem");
					} else if (result['type']['value'] == 'activity') {
						addButtonsToItem(listItem, result, false, true);
						listItem.addClass("activityItem");
					}

					listItem.attr('id', id);
					listItem.appendTo(this);
					moveItemToTimelineList(id);
					calcRoute();
				}
			}
		}
	});
	$("#trashcan").droppable({
		accept: ".timeline div",
		activeClass: "trashcanActive",
		drop: function(ev, ui) {
			ui.draggable.remove();
			removeItemFromTimeline(ui.draggable.prop('id'));
		}
	});
});

// Scales some of the divs on the page to ensure a correct presentation.
function scaleWindow() {
	document.getElementById("resultListBox").style.height = (window.innerHeight - document.getElementById("timelineBox").offsetHeight - 80) + "px";
	document.getElementById("map_canvas").style.height = (window.innerHeight - document.getElementById("timelineBox").offsetHeight) + "px";
	document.getElementById("timeline").style.width = (window.innerWidth - document.getElementById("trashcan").offsetWidth - 10) + "px";
	document.getElementById("resultList").style.height = (document.getElementById("resultListBox").offsetHeight - 26 - 16) + "px";
}

// Calls scaleWindow() when the page is loaded.
window.onresize = function(event) {
	scaleWindow();
}
