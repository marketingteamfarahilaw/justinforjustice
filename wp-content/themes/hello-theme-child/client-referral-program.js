jQuery(document).ready(function($) {

	"use strict";

	// Multi Steps Form
	// Step 0 Start
	var name, phone, email, reference;
	$("#btnNext").click(function(){

		name = $('#laptop_raffle_name').val().trim();
		phone = $('#laptop_raffle_phone').val().trim();
		email = $('#laptop_raffle_email').val().trim();
		reference = $('#laptop_raffle_reference').val().trim();

		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if(name.length == "") {
			alert('Please enter your name');
			$('#laptop_raffle_name').focus();
		} else if(phone.length < 10) {
			alert('Please enter a valid phone number');
			$('#laptop_raffle_phone').focus();
		} else if(!regex.test(email)) {
			alert('Please enter a valid email address');
			$('#laptop_raffle_email').focus();
		} else if(reference.length == "") {
			alert('Please enter name of person who referred you');
			$('#laptop_raffle_reference').focus();
		} else {
			$("#step_0").fadeOut("fast");
			$("#step_1").fadeIn("slow");
		}
	});
	// Step 0 End

	// Step 1 Start
	$("[name='beenAccident']").click(function(){
        if($("[name='beenAccident']:checked").val() == "Yes") {
			$("#step_1").fadeOut("fast");
            $("#step_2").fadeIn("slow");
        }
        if($("[name='beenAccident']:checked").val() == "No") {
            $("#step_1").fadeOut("fast");
            $("#step_5").fadeIn("slow");
        }
	});
	// Step 1 End

	// Step 2 Start
	$("[name='hireAttorney']").click(function(){
        if($("[name='hireAttorney']:checked").val() == "No") {
            $("#step_2").fadeOut("fats");
            $("#step_4_another").fadeIn("fats");
        }
        if($("[name='hireAttorney']:checked").val() == "Yes") {
            $("#step_2").fadeOut("fast");
            $("#step_5").fadeIn("slow");
        }
	});
	// Step 2 End

	// Step 3 Start
	$("[name='getAttorney']").click(function(){
        if($("[name='getAttorney']:checked").val() == "No") {
            $("#step_3").fadeOut("fast");
            $("#step_4").fadeIn("slow");
        }
	});
	// Step 3 End

	// Step 3 Start Another
	$("#btnNextWhyNote").click(function(){

		$("#step_4_another").fadeOut("fast");
		$("#step_5").fadeIn("slow");

	});
	// Step 3 End Another


	// Step 4 Start
	$("[name='anyoneElse']").click(function(){
        if($("[name='anyoneElse']:checked").val() == "Yes") {
            $("#step_5").fadeOut("fast");
            $("#step_3").fadeIn("slow");
        }
	});
	// Step 4 End

	// Current URL
	$('.url').val(window.location.href);

	// Get the form.
	var form = $('#laptop_raffle_form');

	// Get The submit button
	var submitBtn = $('#laptop_raffle_submit');

	// Get the messages div.
	var responseMsg = $('#responseMsg');

	// Set up an event listener for the contact form.
	$(form).submit(function(e) {
		// Stop the browser from submitting the form.
		e.preventDefault();

		// Make the button disabled
		$(submitBtn).attr('disabled', 'disabled');

		// Show the processing message to the user
		$(responseMsg).text('We\'re saving your response. Please wait.');
		$(responseMsg).addClass('processing');
		$(responseMsg).removeClass('error');
		$(responseMsg).removeClass('success');

		// Serialize the form data.
		var formData = $(form).serialize();

		// Submit the form using AJAX.
		$.ajax({
			type: 'POST',
			url: $(form).attr('action'),
			data: new FormData(this),
			contentType: false,
			cache: false,
			processData:false
		})
		.done(function(response) {
			// Make the button active
			$(submitBtn).removeAttr('disabled');

			// Make sure that the responseMsg div has the 'success' class.
			$(responseMsg).removeClass('processing');
			$(responseMsg).removeClass('error');
			$(responseMsg).addClass('success');

			// Set the message text.
			$(responseMsg).html(response);

			// Clear the form.
			$('.form-control').val('');
			$('#laptop_raffle_form').hide();

		})
		.fail(function(data) {
			// Make the button active
			$(submitBtn).removeAttr('disabled');

			// Make sure that the responseMsg div has the 'error' class.
			$(responseMsg).removeClass('processing');
			$(responseMsg).removeClass('success');
			$(responseMsg).addClass('error');

			// Set the message text.
			if (data.responseText !== '') {
				$(responseMsg).html(data.responseText);
			} else {
				$(responseMsg).html('Oops! An error occurred and your message could not be sent.');
			}
		});

	});

});