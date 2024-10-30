// (function( $ ) {
	// 'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	/*jQuery(document).ready(function ($) {
	var calendar = $('#calendar').fullCalendar({
        editable: true,
        events: "fetch-event.php",
        displayEventTime: false,
        eventRender: function (event, element, view) {
            if (event.allDay === 'true') {
                event.allDay = true;
            } else {
                event.allDay = false;
            }
        },
        selectable: true,
        selectHelper: true,
        select: function (start, end, allDay) {
            var title = prompt('Event Title:');

            if (title) {
                var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");

                $.ajax({
                    url: 'add-event.php',
                    data: 'title=' + title + '&start=' + start + '&end=' + end,
                    type: "POST",
                    success: function (data) {
                        displayMessage("Added Successfully");
                    }
                });
                calendar.fullCalendar('renderEvent',
                        {
                            title: title,
                            start: start,
                            end: end,
                            allDay: allDay
                        },
                true
                        );
            }
            calendar.fullCalendar('unselect');
        },
        
        editable: true,
        eventDrop: function (event, delta) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                    $.ajax({
                        url: 'edit-event.php',
                        data: 'title=' + event.title + '&start=' + start + '&end=' + end + '&id=' + event.id,
                        type: "POST",
                        success: function (response) {
                            displayMessage("Updated Successfully");
                        }
                    });
                },
        eventClick: function (event) {
            var deleteMsg = confirm("Do you really want to delete?");
            if (deleteMsg) {
                $.ajax({
                    type: "POST",
                    url: "delete-event.php",
                    data: "&id=" + event.id,
                    success: function (response) {
                        if(parseInt(response) > 0) {
                            $('#calendar').fullCalendar('removeEvents', event.id);
                            displayMessage("Deleted Successfully");
                        }
                    }
                });
            }
        }

    });
	});*/

// })( jQuery );

jQuery(document).ready(function($){
	$(document).on('submit', 'form[name=booking_form]', function(e){
		e.preventDefault();
		//var unique_identifier = $('input[name="unique_identifier"]').val();
		var nonce = $('#booking_form_field').val();
		var data = $(this).serializeArray();
		data.push({name: 'action', value: 'save_bookings'});
		data.push({name: 'nonce', value: nonce});
		//data.push({name: 'unique_identifier', value: unique_identifier});
		$.post( booking_appointment.ajaxurl, data, function( data ) {
			if(data){
				// alert(data);
				jQuery('#demo-modal').html(data);
				// window.location = window.location;
			}
		});
	});
});

document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');
	var calendar = new FullCalendar.Calendar(calendarEl, {
	  headerToolbar: {
		left: 'prevYear,prev,next,nextYear today',
		center: 'title',
		right: 'timeGridDay,dayGridMonth,dayGridWeek'
	  },
	  timeZone: booking_appointment.timezone,
	  initialDate: '2024-03-13',
	  slotDuration: '00:30:00',
	  slotEventOverlap: false,
	  firstDay : 1,
	  selectable: true,
	  eventClick: function(info) {
		// alert('Event: ' + info.event.title);
		// alert('Coordinates: ' + info.jsEvent.pageX + ',' + info.jsEvent.pageY);
		// alert('View: ' + info.view.type);

		// change the border color just for fun
		// info.el.style.borderColor = 'red';
		if(info.el.classList.contains('holiday'))
			return false;
		var data = [];
		data.push({name: 'action', value: 'get_event_booking_form'});
		data.push({name: 'nonce', value: booking_appointment.modal_nonce});
		data.push({name: 'title', value: info.event.title});
		data.push({name: 'start', value: info.event.start});
		data.push({name: 'end', value: info.event.end});
		data.push({name: 'date', value: info.event.startStr});
		jQuery.post( booking_appointment.ajaxurl, data, function( data ) {
			if(data){
				jQuery('#demo-modal').html(data);
				var modal = new Custombox.modal({
				  content: {
					effect: 'fadein',
					target: '#demo-modal'
				  }
				});
				modal.open();
				// window.location = window.location;
			}
		});
	  },

	  editable:false,
	  businessHours: true,
	  dayMaxEvents: true, // allow "more" link when too many events
	  eventSources: [{
			url : booking_appointment.ajaxurl+'?action=get_events',
			ignoreTimezone: true,
			allDayDefault: false
	  }],
	  eventSourceSuccess: function(content, response) {
		return content.eventArray;
	  }
	});
	calendar.render();
});