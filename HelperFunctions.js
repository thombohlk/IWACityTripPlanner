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
			var $li = $('<div class="timelineItem" >').html(ui.draggable.html());
			$li.attr('id', ui.draggable.prop('id'));
			$li.appendTo(this);
			moveItemToTimelineList(ui.draggable.prop('id'));
			calcRoute();
		}
	});
	$("#trashcan").droppable({
		accept: ".timeline div",
		hoverClass: "ui-state-hover",
		drop: function(ev, ui) {
		    ui.draggable.remove();
			removeItemFromTimeline(ui.draggable.prop('id'));
	}
});
});

function scaleWindow() {
	document.getElementById("resultListBox").style.height = (window.innerHeight - document.getElementById("timelineBox").offsetHeight - 80) + "px";
	document.getElementById("map_canvas").style.height = (window.innerHeight - document.getElementById("timelineBox").offsetHeight) + "px";
	document.getElementById("timeline").style.width = (window.innerWidth - document.getElementById("trashcan").offsetWidth - 10) + "px";
}

window.onresize = function(event) {
	scaleWindow();
}
