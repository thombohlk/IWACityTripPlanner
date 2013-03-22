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
					console.log(resultList[i]);
					text = '<b>' + resultList[i]['title']['value'] + '</b>';
					if (resultList[i]['city']) text+= '<br />' + resultList[i]['city']['value'];
					if (resultList[i]['start']) text+= '<br />' + resultList[i]['start']['value'].replace("Z", "").replace("T", " ");
					if (resultList[i]['end']) text+= '<br />' + resultList[i]['end']['value'].replace("Z", "").replace("T", " ");

					var $li = $('<div class="timelineItem" >').html(text);
					if (resultList[i]['type']['value'] == 'hotel') $li.addClass("hotelItem");
					if (resultList[i]['type']['value'] == 'venue') $li.addClass("venueItem");
					if (resultList[i]['type']['value'] == 'activity') $li.addClass("activityItem");
					$li.attr('id', id);
					$li.appendTo(this);
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
                setMessage(error.statusText, true);
            });  
}

function scaleWindow() {
	document.getElementById("resultListBox").style.height = (window.innerHeight - document.getElementById("timelineBox").offsetHeight - 80) + "px";
	document.getElementById("map_canvas").style.height = (window.innerHeight - document.getElementById("timelineBox").offsetHeight) + "px";
	document.getElementById("timeline").style.width = (window.innerWidth - document.getElementById("trashcan").offsetWidth - 10) + "px";
	document.getElementById("resultList").style.height = (document.getElementById("resultListBox").offsetHeight - 26 - 16) + "px";
}

window.onresize = function(event) {
	scaleWindow();
}
