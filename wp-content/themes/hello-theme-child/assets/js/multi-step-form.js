// Pass the current URL to the form
document.querySelector('.current-url').value = window.location.href

// User Agent
function getDeviceDetails() {
    const userAgent = navigator.userAgent;
    let deviceType = "Computer"; // Default to computer

    if (/Android/i.test(userAgent)) {
        deviceType = "Android";
    } else if (/iPhone|iPad|iPod/i.test(userAgent)) {
        deviceType = "iOS";
    }

    return deviceType;
}
const deviceType = getDeviceDetails();
document.querySelector('.user-agent').value = deviceType;

// Display
document.querySelector('.display').value = screen.width;

// USA Phone format
document.getElementById('phone').addEventListener('input', formatPhoneNumber);

function formatPhoneNumber(event) {
    var input = event.target.value.replace(/\D/g, ''); // Remove all non-numeric characters
    var formattedInput = '';

    // Format the input based on the length
    if (input.length > 3 && input.length <= 6) {
        formattedInput = `(${input.slice(0, 3)}) ${input.slice(3)}`;
    } else if (input.length > 6) {
        formattedInput = `(${input.slice(0, 3)}) ${input.slice(3, 6)}-${input.slice(6, 10)}`;
    } else {
        formattedInput = input;
    }

    event.target.value = formattedInput; // Update the input field
}

// Multi Step Form
let currentStep = 1;
let steps = document.getElementsByClassName('step'); // Define the steps
showStep(currentStep);
showStep(currentStep);

// Attach event listeners to 'Previous' and 'Next' buttons
document.querySelector('.btn-info').addEventListener('click', function() {
	navigate(-1);
});

let continueButtons = document.querySelectorAll('.btn-continue');
continueButtons.forEach(function(button) {
	button.addEventListener('click', function() {
		if (validateForm()) {
			navigate(1);
		}
	});
});

document.querySelector('.btn-primary').addEventListener('click', function() {
	if (currentStep < steps.length && validateForm()) {
		navigate(1);
	} else if (currentStep === steps.length) {
		// If it's the last step, submit the form or perform final actions
        submitForm(); // Trigger form submission on the last step
	}
});

document.querySelector('.btn-submit').addEventListener('click', function(e) {
	e.preventDefault(); // Prevent the default form submission

    // Last Step Validation
    var statusMessageDiv = document.getElementById('formStatusMessage');

    // Phone, Email Validation
    var phone = document.getElementById('phone');
    var email = document.getElementById('email');

    if(phone.value.trim() == "") {
        email.classList.remove('border-danger')
        phone.classList.add('border-danger')
        statusMessageDiv.innerHTML = '<div class="alert alert-danger rounded-1" role="alert"><i class="bi bi-exclamation-triangle"></i> Please enter your phone No.</div>';
    } else if(!validateEmail(email.value)) {
        phone.classList.remove('border-danger')
        email.classList.add('border-danger')
        statusMessageDiv.innerHTML = '<div class="alert alert-danger rounded-1" role="alert"><i class="bi bi-exclamation-triangle"></i> Please enter a valid email.</div>';
    } else {
        statusMessageDiv.innerHTML = "";
        submitForm(); // Trigger form submission from the separate submit button
    }

});

function submitForm() {
    let form = document.getElementById('multiStepForm');
    let formData = new FormData(form);
    let actionUrl = form.getAttribute('action'); // Retrieve the action attribute from the form
    let statusMessageDiv = document.getElementById('formStatusMessage');
    let submitButton = document.querySelector('.btn-submit'); // Adjust the selector if necessary
    let nextButton = document.querySelector('.btn-primary'); // Adjust the selector if necessary

    submitButton.disabled = true;
    nextButton.disabled = true;
    submitButton.innerHTML = '<i class="bi bi-arrow-repeat spinner"></i>';
    submitButton.classList.add('py-1');

    fetch(actionUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log(text); // Check what the actual full response text is
        return JSON.parse(text); // Then parse it manually
    })
    .then(data => {
        // Handle response here
        console.log(data);
        if (data.status === 'success') {
            submitButton.innerHTML = "Submit";
            submitButton.classList.remove('py-1');
            statusMessageDiv.innerHTML = '<div class="alert alert-success rounded-1" role="alert"><i class="bi bi-check-circle"></i> Your message has been sent successfully!</div>';
            // Optionally, redirect or reset the form
            window.location.assign('/why-hire-farahi-law-thank-you-page')
        } else {
            submitButton.innerHTML = "Submit";
            submitButton.classList.remove('py-1');
            statusMessageDiv.innerHTML = `<div class="alert alert-danger rounded-1" role="alert"><i class="bi bi-exclamation-triangle"></i> Submission failed: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.log('Error:', error);
        submitButton.innerHTML = "Submit";
        submitButton.classList.remove('py-1');
        statusMessageDiv.innerHTML = `<div class="alert alert-danger rounded-1" role="alert"><i class="bi bi-exclamation-triangle"></i> Submission failed: ${error.message}</div>`;
    })
    .finally(() => {
        // Re-enable buttons
        submitButton.disabled = false;
        nextButton.disabled = false;
    });
}

// Attach event listeners to radio buttons for automatic navigation
let radioButtons = document.querySelectorAll('input[type="radio"]');
radioButtons.forEach(function(radio) {
	radio.addEventListener('click', function() {
		if (validateForm()) {
			setTimeout(() => navigate(1), 1); // Adding a delay for better UX
		}
	});
});

const totalSteps = 8 + 1 ; // Add 1 step extra so that in the last step it will not show 100%

function navigate(stepMove) {
	currentStep += stepMove;
	showStep(currentStep);
    updateProgressBar(currentStep, totalSteps); // Update the progress bar
}

function updateProgressBar(step, totalSteps) {
    const progressPercentage = (step / totalSteps) * 100;
    const progressBar = document.querySelector('.progress-bar');

    progressBar.style.width = progressPercentage + '%';
    progressBar.setAttribute('aria-valuenow', progressPercentage);
    progressBar.textContent = Math.round(progressPercentage) + '%'; // Update the text content
}

// Call this function every time the step changes
updateProgressBar(currentStep, totalSteps);

function showStep(step) {
	let steps = document.getElementsByClassName('step');
	let prevButton = document.querySelector('.btn-info');
	let nextButton = document.querySelector('.btn-primary');

	// Remove active class from all steps and hide them
	Array.from(steps).forEach(function(stepElement) {
		stepElement.classList.remove('active-step');
	});

	if (step > steps.length) {
		document.getElementById('multiStepForm').submit();
		return;
	}

	if (steps[step - 1]) {
		steps[step - 1].classList.add('active-step');
	}

	// Update button visibility
	prevButton.style.display = step === 1 ? 'none' : 'inline-block';
	nextButton.innerHTML = step === steps.length ? '<i class="bi bi-chevron-right"></i>' : '<i class="bi bi-chevron-right"></i>';
}

function validateForm() {
    let currentStepEl = document.querySelector('.step.active-step');
    let stepIndex = Array.from(document.getElementsByClassName('step')).indexOf(currentStepEl);
    let errorMessage = currentStepEl.querySelector('.error-message');

    // Reset error message
    errorMessage.style.display = 'none';

    switch (stepIndex) {
        case 0: // Validation for Step 1
            return whereHappenValidation(currentStepEl, errorMessage);
        case 1: // Validation for Step 1
            return radioValidation(currentStepEl, errorMessage);
        case 2: // Validation for Step 2
            return radioValidation(currentStepEl, errorMessage);
        case 3: // Validation for Step 3
            return radioValidation(currentStepEl, errorMessage);
        case 4: // Validation for Step 4
            return radioValidation(currentStepEl, errorMessage);
        case 5: // Validation for Step 5
            return radioValidation(currentStepEl, errorMessage);
        case 6: // Validation for Step 6
            return nameValidation(currentStepEl, errorMessage);
        case 7: // Validation for Step 7
            return radioValidation(currentStepEl, errorMessage);
        case 8: // Validation for Step 8
            return radioValidation(currentStepEl, errorMessage);
        case 9: // Validation for Step 9
            return radioValidation(currentStepEl, errorMessage);
        case 10: // Validation for Step 10
            return radioValidation(currentStepEl, errorMessage);
        case 11: // Validation for Step 11
            return caseDetailsValidation(currentStepEl, errorMessage);
        // Add more cases for additional steps
        default:
            return true; // No validation for steps without specific rules
    }
}

function whereHappenValidation(stepEl, errorMessage) {
    let where_happen = stepEl.querySelector('#where_happen').value;
    if(where_happen.trim() == "") {
        errorMessage.innerHTML = '<div class="alert alert-danger rounded-1 mt-3" role="alert"><strong><i class="bi bi-exclamation-triangle"></i> Oops!</strong> Where did the accident happen?</div>';
        errorMessage.style.display = 'block';
        return false;
    }

    return true;
}

function nameValidation(stepEl, errorMessage) {
    let first_name = stepEl.querySelector('#first_name').value;
    let last_name = stepEl.querySelector('#last_name').value;
    if(first_name.trim() == "") {
        errorMessage.innerHTML = '<div class="alert alert-danger rounded-1 mt-3" role="alert"><strong><i class="bi bi-exclamation-triangle"></i> Oops!</strong> Please enter your first name</div>';
        errorMessage.style.display = 'block';
        return false;
    } else if(last_name.trim() == "") {
        errorMessage.innerHTML = '<div class="alert alert-danger rounded-1 mt-3" role="alert"><strong><i class="bi bi-exclamation-triangle"></i> Oops!</strong> Please inter your last name</div>';
        errorMessage.style.display = 'block';
        return false;
    }

    return true;
}

function radioValidation(stepEl, errorMessage) {
    let selectedRadio = stepEl.querySelector('input[type="radio"]:checked');
    if (!selectedRadio) {
        errorMessage.innerHTML = '<div class="alert alert-danger rounded-1 mt-3" role="alert"><strong><i class="bi bi-exclamation-triangle"></i> Oops!</strong> Please make a selection</div>';
        errorMessage.style.display = 'block';
        return false;
    }

    // Check if 'No one was hurt' is selected in step 3
    if (stepEl.id === 'step1' && selectedRadio.value === 'No one was hurt') {

        currentStep = 5; // Set current step 5
        showStep(currentStep); // Go to step 5
        updateProgressBar(currentStep, totalSteps);

        return false;
    }

    // Check if 'No' police report is selected in step 6
    if (stepEl.id === 'step4' && selectedRadio.value === 'No') {

        currentStep = 7; // Set current step 7
        showStep(currentStep); // Go to step 7
        updateProgressBar(currentStep, totalSteps);

        return false;
    }

    return true;
}

function caseDetailsValidation(stepEl, errorMessage) {
    let caseDetails = stepEl.querySelector('#describe_details').value;

    if(caseDetails.trim() == "") {
        errorMessage.innerHTML = '<div class="alert alert-danger rounded-1 mt-3" role="alert"><strong><i class="bi bi-exclamation-triangle"></i> Oops!</strong> Please describe your accident in details.</div>';
        errorMessage.style.display = 'block';
        return false;
    }
    return true;
}

function emailValidation(stepEl, errorMessage) {
    
	let email = stepEl.querySelector('input[type="email"]').value;
	
	if (!email.trim()) {
        errorMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Please enter your email address.';
        errorMessage.style.display = 'block';
        return false;
    }

    if (!validateEmail(email)) {
        errorMessage.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Please enter a valid email address.';
        errorMessage.style.display = 'block';
        return false;
    }

	errorMessage.style.display = 'none';
    return true;
}

function validateEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return emailRegex.test(email);
}