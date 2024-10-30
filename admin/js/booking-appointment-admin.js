(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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
	
	$(document).on('submit', 'form#frm_email_settings', function(e){
		e.preventDefault();
		var data = $(this).serializeArray();
		data.push({name: 'action', value: 'save_emailsettings'});
		$.post( ajaxurl, data, function( data ) {
			if(data){
				// alert(data);
				window.location = window.location;
			}
		});
	});
	$(document).on('submit', 'form#frm_settings', function(e){
		e.preventDefault();
		var data = $(this).serializeArray();
		data.push({name: 'action', value: 'save_settings'});
		$.post( ajaxurl, data, function( data ) {
			if(data){
				// alert(data);
				window.location = window.location;
			}
		});
	});
	$(document).on('submit', 'form#frm_payments', function(e){
		e.preventDefault();
		var data = $(this).serializeArray();
		data.push({name: 'action', value: 'save_payment_settings'});
		$.post( ajaxurl, data, function( data ) {
			if(data){
				// alert(data);
				window.location = window.location;
			}
		});
	});
	$(document).on('submit', 'form#frm_configure', function(e){
		e.preventDefault();
		var data = $(this).serializeArray();
		data.push({name: 'action', value: 'save_configuration'});
		$.post( ajaxurl, data, function( data ) {
			if(data){
				// alert(data);
				window.location = window.location;
			}
		});
	});
	$(document).on('change', 'select[name=duration]', function(){
		var value = $(this).val();
		if(value=='other'){
			$('<tr class="custom_duration"><th>Custom Duration</th><td><input type="number" value="1" min="0" max="23" name="hours" required /> Hours <input type="number" value="1" min="0" max="59" name="minutes" required /> Minutes</td></tr>').insertAfter($(this).closest('tr'));
		}else{
			$('tr.custom_duration').remove();
		}
	});
	$(document).on('change', 'select[name=working_hours]', function(){
		var value = $(this).val();
		if(value=='other'){
			$('<tr class="custom_hours"><th>Custom Working Hours</th><td><input type="time" id="working_hours_start" name="working_hours_start" min="00:00" max="24:00" required /> to <input type="time" id="working_hours_end" name="working_hours_end" min="00:00" max="24:00" required /></td></tr><tr class="break"><th>Want to add Break?</th><td><input type="checkbox" name="break" value="1" /></td></tr>').insertAfter($(this).closest('tr'));
		}else{
			$('tr.custom_hours').remove();
		}
	});
	$(document).on('change', 'input[name="break"]', function(){
		var ischecked= $(this).is(':checked');
		if(!ischecked){
			$('tr.break').remove();
		}else{
			var min = $('input[name=working_hours_start]').val();
			var max = $('input[name=working_hours_end]').val();
			$('<tr class="custom_breaks"><th>Break</th><td><input type="time" id="break_start" name="break_start" min="'+min+'" max="'+max+'" required /> to <input type="time" id="break_end" name="break_end" min="'+min+'" max="'+max+'" required /></td></tr>').insertAfter($(this).closest('tr'));
		}
	});
	 /*$('#save_emailsettings').on('click', function(){
		var from_email = $('input[name=from_email]').val();
		var from_name = $('input[name=from_name]').val();
		var replyto_email = $('input[name=replyto_email]').val();
		var replyto_name = $('input[name=replyto_name]').val();
		var email_subject = $('input[name=email_subject]').val();
		var editor = tinyMCE.get('email_body');
		if (editor) {
			content = editor.getContent();
		} else {
			content = $('#email_body').val();
		}
		$('#save_newsletter_loader').show();
		var form_data = new FormData();
		form_data.append('action', 'save_emailsettings');
		form_data.append('from_email', from_email);
		form_data.append('from_name', from_name);
		form_data.append('replyto_email', replyto_email);
		form_data.append('replyto_name', replyto_name);
		form_data.append('email_subject', email_subject);
		form_data.append('email_body', content);
		jQuery.ajax({
			url: ajaxurl,
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: 'POST',
			success: function(data) {
				$('#save_emailsettings_loader').hide();
			},
			error: function(error) {
				console.log(error);
			}
		});
	});*/
	 

})( jQuery );

function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
	tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
	tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}