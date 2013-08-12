
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
					addItemToTimeLine(resultList[i]);
					moveItemToTimelineList(resultList[i]['id']['value']);
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

$(document).ready(function() {
	$("#loginForm input:button").click(function() {
		hideLoginPopup();
		// Set loading animation
		$("body").addClass("loading");
		
		$.ajax({
			type: "POST",
			url: 'Login.php',
			data: $('#loginForm').serialize(),
			dataType: 'json',
			success: function(data) {
				setMessage("Welcome " + data.name + "!", false);
				$("#loginButton").addClass("hidden");
				$("#logoutButton").removeClass("hidden");
				clearTimeLine();
				buildTimeLine(data.cityTrip);
			},
			statusCode: {
				403: function(e) {
					setMessage(e.responseText, true);
				}
			},
			complete: function() {
				// Remove loading animation
				$("body").removeClass("loading");
			}
		});
		return false;
	});
	
	
	$("#signUpForm input:button").click(function() {
		hideSignUpPopup();
		// Set loading animation
		$("body").addClass("loading");
		
		$.ajax({
			type: "POST",
			url: 'SignUp.php',
			data: $('#signUpForm').serialize(),
			dataType: 'json',
			success: function(data) {
				setMessage("Welcome " + data.name + "!", false);
			},
			statusCode: {
				403: function(e) {
					setMessage(e.responseText, true);
				}
			},
			complete: function() {
				// Remove loading animation
				$("body").removeClass("loading");
			}
		});
		return false;
	});
});
