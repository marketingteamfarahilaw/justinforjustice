<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

	if(is_page(array('190219', '180553', '176596', '178807'))) {
		wp_register_script('laptop_raffle', get_stylesheet_directory_uri() . '/laptop-raffle-form.js', array('jquery'), '1.0.1', true);
		wp_enqueue_script('laptop_raffle');
	}

	// 
	if(is_page(array('193671'))) {
		wp_register_script('client_referral_program', get_stylesheet_directory_uri() . '/client-referral-program.js', array('jquery'), '1.0.0', true);
		wp_enqueue_script('client_referral_program');
	}

	// Multi Step Form
	wp_register_style('bootstrap-grid-css', get_stylesheet_directory_uri() . '/assets/css/bootstrap-grid.min.css');
	wp_register_style('multi-step-form-css', get_stylesheet_directory_uri() . '/assets/css/multi-step-form.css', '', time());
	wp_register_script('multi-step-form-js',  get_stylesheet_directory_uri() . '/assets/js/multi-step-form.js', '', '1.0.0', true);
	wp_register_script('california-address-js',  get_stylesheet_directory_uri() . '/assets/js/california-address.js', '', '1.0.0', true);
	
	// LA Fire Claims
	wp_register_script('la-fire-claims-multi-step-form-js',  get_stylesheet_directory_uri() . '/assets/js/la-fire-claims-multi-step-form.js', '', time(), true);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

// Elementor Custom Form Validation
add_action( 'elementor_pro/forms/validation', function($record, $ajax_handler) {
    // Define an array of field IDs you want to validate
    $field_ids = ['first_name', 'last_name', 'name', 'name_1', 'name_2', 'phone', 'message', 'message_1', 'message_2', 'acciden_type', 'physical_injuey', 'case_type'];

    // Pattern to match URLs
    $url_pattern = '/\b(?:https?:\/\/|www\.)\S+\b/i';

    // Fetch blocked words from GitHub repository
    $blocked_users_ip_json = 'https://raw.githubusercontent.com/hasan4flf/blocked-users/main/blocked-words.json';
    $response = wp_remote_get($blocked_users_ip_json);
    $decoded_data = [];

    if (is_array($response) && !is_wp_error($response)) {
        $json_data = wp_remote_retrieve_body($response);
        $decoded_data = json_decode($json_data, true); // Decode as an associative array
    }

    // Iterate over each field ID
    foreach ($field_ids as $field_id) {
        $fields = $record->get_field( [
            'id' => $field_id,
        ]);

        if (empty($fields)) {
            continue; // Skip if field is not found
        }

        $field = current($fields);

        // Perform URL pattern check
        if (preg_match($url_pattern, $field['value'])) {
            $ajax_handler->add_error($field['id'], "Please don't include URLs.");
            continue; // Skip further checks for this field
        }

        // Check against blocked words
        foreach ($decoded_data as $searchItem) {
            if (stripos(strtolower($field['value']), $searchItem['word']) !== false) {
                $ajax_handler->add_error($field['id'], "Your input contains blocked words.");
                break; // Stop checking other blocked words for this field
            }
        }
    }
}, 10, 2);

// Phone Number Validation for multiple phone fields
add_action( 'elementor_pro/forms/validation', function($record, $ajax_handler){
    // Array of phone field IDs you want to validate
    $phone_field_ids = ['phone', 'phone_1', 'phone_2'];

    // Loop through each phone field ID and apply the validation
    foreach ($phone_field_ids as $field_id) {
        $fields = $record->get_field([
            'id' => $field_id,
        ]);

        if ( empty( $fields ) ) {
            continue; // Skip if the field is not present
        }

        $field = current( $fields );

        // Remove all non-digits from the phone number
        $phone = preg_replace('/\D+/', '', $field['value']);

        // Remove country code if it starts with '1' and is 11 digits long (for US numbers)
        if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
            $phone = substr($phone, 1);
        }

        // Validate if the phone number is exactly 10 digits long
        if (strlen($phone) == 10) {
            // Format the phone number
            $formatted_phone = '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
            // Optionally, you can replace the original value with the formatted one
            // $record->update_field($field_id, $formatted_phone);
        } else {
            // If the phone number is invalid, add an error message
            $ajax_handler->add_error($field['id'], "Invalid phone number");
        }
    }
}, 10, 2 );

// Block User Email, IP, Country to being Elementor form submit
add_action( 'elementor_pro/forms/validation', function ( $record, $ajax_handler ) {
	// Array of email field IDs you want to validate
    $email_field_ids = ['email', 'email_1', 'email_2'];

	// Loop through each email field ID and apply the validation
    foreach ($email_field_ids as $field_id) {
        $fields = $record->get_field([
            'id' => $field_id,
        ]);

        if ( empty( $fields ) ) {
            continue; // Skip if the field is not present
        }

        $field = current( $fields );

		// Get the current user IP
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$current_user_ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$current_user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$current_user_ip = $_SERVER['REMOTE_ADDR'];
		}

		// Blocked IP's will be loaded from a github repository: https://github.com/hasan4flf/blocked-users
		$blocked_users_ip_json = 'https://raw.githubusercontent.com/hasan4flf/blocked-users-ip/main/blocked-ip.json';
		$response = wp_remote_get($blocked_users_ip_json);

		if (is_array($response) && !is_wp_error($response)) {
			$json_data = wp_remote_retrieve_body($response);
		} else {
			return;
		}

		if (strpos($json_data, $current_user_ip) !== false) {
			$ajax_handler->add_error($field['id'], 'Something went wrong. Error Code: 0001');
			return;
		}

		/**
		 * Blocked Emails Checking
		 * Fetch blocked words from GitHub repository
		 */
		$blocked_users_ip_json = 'https://raw.githubusercontent.com/hasan4flf/blocked-users/main/blocked-emails.json';
		$response = wp_remote_get($blocked_users_ip_json);
		$decoded_data = [];

		if (is_array($response) && !is_wp_error($response)) {
			$json_data = wp_remote_retrieve_body($response);
			$decoded_data = json_decode($json_data, true); // Decode as an associative array
		}

		// Check against blocked words
		foreach ($decoded_data as $searchItem) {
			if (stripos(strtolower($field['value']), $searchItem['word']) !== false) {
				$ajax_handler->add_error($field['id'], "Your input contains blocked words.");
				break; // Stop checking other blocked words for this field
			}
		}

		/**
		 * Blocked Countries
		 */
		//Token
		$token = "e4d21a1626faee";

		// Construct the URL
		$url = "https://ipinfo.io/{$current_user_ip}?token={$token}";

		// Fetch the content from the URL
		$response = file_get_contents($url);

		// Check if the request was successful
		if ($response === FALSE) {
			// $ajax_handler->add_error($field['id'], "Error fetching data.");
		} else {
			// Decode the JSON response
			$data = json_decode($response, true);

			if($data['country']) {
				$user_country = $data['country'];
			} elseif($data['country'] == null) {
				$user_country = "no_country";
			}

			// Check if json_decode() failed
			if (json_last_error() !== JSON_ERROR_NONE) {
				// $ajax_handler->add_error($field['id'], json_last_error_msg());
			} else {
				$blocked_users_countries = 'https://raw.githubusercontent.com/hasan4flf/blocked-users-ip/main/blocked-countries.json';
				$response = wp_remote_get($blocked_users_countries);

				if (is_array($response) && !is_wp_error($response)) {
					$json_data = wp_remote_retrieve_body($response);
				} else {
					return;
				}

				if (strpos($json_data, $user_country) !== false) {
					$ajax_handler->add_error($field['id'], "Something went wrong. Error Code: 0002");
					return;
				}
			}
		}
    }
}, 10, 2 );

// Multi Step Form Shortcode
function jfj_multi_step_form_shortcode($atts)
{
    // Enqueue script and style
	wp_enqueue_style('bootstrap-grid-css');
	wp_enqueue_style('multi-step-form-css');
	wp_enqueue_script('multi-step-form-js');
	wp_enqueue_script('california-address-js');

	$theme_url = get_stylesheet_directory_uri();

    // Your shortcode functionality here
    return "
		<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css'>
		<section class='get-started'>
			<div class='container'>
				<div class='row justify-content-center'>
					<div class='col-12'>
						<form method='post' action='https://womensrightsgroup.com/phpmailer/brain-injury-form/process-lead.php' id='multiStepForm' class='multi-step-form'>
		
							<input type='hidden' class='current-url' name='referrer'>
							<input type='hidden' class='user-agent' name='user_agent'>
							<input type='hidden' class='display' name='display'>
		
							<div class='progress mb-5' role='progressbar' aria-label='Progress bar example' aria-valuenow='0'
								aria-valuemin='0' aria-valuemax='100'>
								<div class='progress-bar progress-bar-striped progress-bar-animated'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step0' class='single-step text-center step'>
								<label for='where_happen' class='main-label text-center'>Where did the accident happen?</label>
								<input type='text' class='form-control form-control-lg rounded-1' name='where_happen' id='where_happen' placeholder='Zip or City'>
								<div class='text-center'>
									<button type='button' class='btn btn-lg btn-continue text-uppercase'>Next</button>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step1' class='single-step step'>
								<label for='' class='main-label'>Who was hurt in the accident?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='i_was_hurt' name='who_was_hurt' value='I was hurt'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='i_was_hurt'>I was hurt</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='a_loved_one_was_hurt' name='who_was_hurt' value='A loved one was hurt'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='a_loved_one_was_hurt'>A loved one was hurt</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='we_were_both_hurt' name='who_was_hurt' value='We were both hurt'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='we_were_both_hurt'>We were both hurt</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='no_one_was_hurt' name='who_was_hurt' value='No one was hurt'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='no_one_was_hurt'>No one was hurt</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step2' class='single-step step'>
								<label for='' class='main-label'>Did the injured person receive treatment?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='treated_at_hospital' name='receive_treatment' value='Treated at a hospital'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='treated_at_hospital'>Treated at a hospital</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='treated_at_doctor_office' name='receive_treatment' value='Treated at a doctor's office'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='treated_at_doctor_office'>Treated at a doctor's office</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='was_not_treated' name='receive_treatment' value='Was not treated'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='was_not_treated'>Was not treated</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step3' class='single-step step'>
								<label for='' class='main-label'>What is the primary injury?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='anxiety' name='primary_injury' value='Anxiety'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='anxiety'>Anxiety</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='back_neck_pain' name='primary_injury' value='Back or Neck Pain'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='back_neck_pain'>Back or Neck Pain</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='broken_bones' name='primary_injury' value='Broken Bones'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='broken_bones'>Broken Bones</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='cuts_and_bruises' name='primary_injury' value='Cuts and Bruises'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='cuts_and_bruises'>Cuts and Bruises</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='headaches' name='primary_injury' value='Headaches'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='headaches'>Headaches</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='memory_loss' name='primary_injury' value='Memory Loss'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='memory_loss'>Memory Loss</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='loss_of_limb' name='primary_injury' value='Loss of Limb'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='loss_of_limb'>Loss of Limb</label>
										</div>
									</div>
									<div class='col-md-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='other' name='primary_injury' value=oOther'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='other'>Other</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step4' class='single-step step'>
								<label for='' class='main-label'>Was a police report filed?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='police_report_yes' name='police_report' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='police_report_yes'>Yes</label>
										</div>
									</div>
									<div class='col-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='police_report_no' name='police_report' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='police_report_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step5' class='single-step step'>
								<label for='' class='main-label'>Did the police report say you are at fault?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='your_fault_yes' name='your_fault' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='your_fault_yes'>Yes</label>
										</div>
									</div>
									<div class='col-6'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='your_fault_no' name='your_fault' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='your_fault_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step6' class='single-step step'>
								<label for='' class='main-label' id='main_label_last'>What is your name?</label>
								<div class='label-description' id='labe_description_last'>Personal Information Is Safe & Secure.</div>
								<div class='row'>
									<div class='col-6'>
										<input type='text' class='form-control form-control-lg rounded-1' name='first_name' id='first_name' placeholder='First Name'>
									</div>
									<div class='col-6'>
										<input type='text' class='form-control form-control-lg rounded-1' name='last_name' id='last_name' placeholder='Last Name'>
									</div>
								</div>
								<div class='text-center'>
									<button type='button' class='btn btn-lg btn-continue text-uppercase'>Next</button>
								</div>
								<div class='error-message'></div>
							</div>
		
							<!-- Single Step -->
							<div id='step7' class='single-step step'>
								<label for='' class='main-label' id='main_label_last'>Contact Details</label>
								<div class='label-description' id='labe_description_last'>Your Privacy Matters to Us - Zero Tolerance for Spam.</div>
								<div class='row g-3'>
									<div class='col-12'>
										<input type='tel' class='form-control form-control-lg rounded-1' name='phone' id='phone' placeholder='Phone Number' required>
									</div>
									<div class='col-12'>
										<input type='email' class='form-control form-control-lg rounded-1' name='email' id='email' placeholder='Email Address' required>
									</div>
									<div class='col-12'>
										<div class='form-check d-flex align-items-start'>
											<input class='form-check-input' type='checkbox' name='terms_condition' value='Terms and Condition' id='terms_condition' checked>
											<label class='label-term-condition form-check-label m-0 p-0' for='terms_condition'>
												By submitting this form, I consent to receiving text messages and emails from Farahi Law Firm.
											</label>
										</div>
									</div>
									<div class='col-12'>
										<input type='hidden' name='form_fields[navigation_history]'>
									</div>
									<div class='col-12'>
										<button type='submit' class='btn btn-lg btn-submit'>Submit</button>
									</div>
								</div>
							</div>
		
							<div id='formStatusMessage' class='mt-3'></div>
		
							<div class='buttons d-flex gap-3 g-3 align-items-center'>
								<button type='button' class='btn btn-info'><i class='bi bi-chevron-left'></i></button>
								<button type='button' class='btn btn-primary'><i class='bi bi-chevron-right'></i></button>
								<a href='https://justinforjustice.com/' target='_blank' class='btn btn-flf' style='display:none'>Powered by
									<span>JFJ</span></a>
							</div>
		
						</form>
					</div>
				</div>
			</div>
		</section>
    ";
}
add_shortcode('jfj_multi_step_form', 'jfj_multi_step_form_shortcode');

// LA Fire Claims Multi Step Form Shortcode
function jfj_la_fire_multi_step_form_shortcode($atts)
{
    // Enqueue script and style
	wp_enqueue_style('bootstrap-grid-css');
	wp_enqueue_style('multi-step-form-css');
	wp_enqueue_script('la-fire-claims-multi-step-form-js');

	$theme_url = get_stylesheet_directory_uri();

    // Your shortcode functionality here
    return "
		<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css'>
		<section class='get-started'>
			<div class='container'>
				<div class='row justify-content-center'>
					<div class='col-lg-12 my-5'>
						<form method='post' action='#' id='multiStepForm' class='multi-step-form la-fire-multi-step-form' enctype='multipart/form-data'>

							<input type='hidden' class='current-url' name='referrer'>
							<input type='hidden' class='user-agent' name='user_agent'>
							<input type='hidden' class='display' name='display'>

							<div class='progress mb-5' role='progressbar' aria-label='Progress bar example' aria-valuenow='0'
								aria-valuemin='0' aria-valuemax='100'>
								<div class='progress-bar progress-bar-striped progress-bar-animated'></div>
							</div>

							<!-- Single Step -->
							<div id='step0' class='single-step step'>
								<label for='' class='large-label'>EXPERT LEGAL CONSULTATION FOR FIRE DAMAGE CLAIMS</label>
								<label for='' class='main-label'>Have you been affected by <br> the fire in Los Angeles?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='affected_yes' name='affected' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='affected_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='affected_no' name='affected' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='affected_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step1' class='single-step step'>
								<label for='' class='main-label' id='main_label_last'>Contact Details</label>
								<div class='row g-3'>
									<div class='col-12'>
										<input type='text' class='form-control form-control-lg rounded-1' name='name' id='name' placeholder='Full Name'>
									</div>
									<div class='col-12'>
										<input type='tel' class='form-control form-control-lg rounded-1' name='phone' id='phone' placeholder='Phone Number' required>
									</div>
									<div class='col-12'>
										<input type='email' class='form-control form-control-lg rounded-1' name='email' id='email' placeholder='Email Address (Optional)'>
									</div>
								</div>
								<div class='text-center'>
									<button type='button' class='btn btn-lg btn-continue text-uppercase'>Next</button>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step2' class='single-step step'>
								<label for='' class='main-label'>Has your home, property, or business sustained damage as a result of the fire?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='damage_yes' name='damage' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='damage_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='damage_no' name='damage' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='damage_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step3' class='single-step step'>
								<label for='' class='main-label'>Have you suffered injury or displacement from the fire?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='injured_yes' name='injured' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='injured_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='injured_no' name='injured' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='injured_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step4' class='single-step step'>
								<label for='' class='main-label'>Do you want us to do a FREE additional living expenses (ALE) review as well?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='living_expense_yes' name='living_expense' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='living_expense_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='living_expense_no' name='living_expense' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='living_expense_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step5' class='single-step step'>
								<div class='row g-3'>
									<div class='col-12'>
										<label for='' class='large-label'>Get an Expedited,<br>FREE Policy Review</label>
										<label for='insurance_policy' class='main-label'>Upload Your Insurance Policy Now!</label>
										<input type='file' class='form-control' name='insurance_policy' id='insurance_policy' accept='.pdf,.jpg,.png,.gif'>
									</div>
									<div class='col-12 text-center'>
										<label for='' class='disclaimer-label'>The sooner we have your information, the faster we can help you secure your insurance payout and address immediate needs, including ALEs like temporary housing and other expenses.</label>
									</div>
									<div class='col-12' style='text-align:center'>
										<div class='form-check d-flex align-items-start'>
											<input class='form-check-input' type='checkbox' name='terms_condition' value='Terms and Condition' id='terms_condition' checked>
											<label class='label-term-condition form-check-label m-0 p-0' for='terms_condition'>
												By submitting this form, I consent to receiving text messages and emails from Farahi Law Firm.
											</label>
										</div>
									</div>
									<div class='col-12' style='text-align:center'>
										<button type='submit' class='btn btn-lg btn-submit'>Submit</button>
									</div>
								</div>
							</div>

							<div id='formStatusMessage' class='mt-3'></div>

							<div class='buttons d-flex gap-3 g-3 align-items-center'>
								<button type='button' class='btn btn-info'><i class='bi bi-chevron-left'></i></button>
								<button type='button' class='btn btn-primary'><i class='bi bi-chevron-right'></i></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</section>
    ";
}
add_shortcode('jfj_la_fire_multi_step_form', 'jfj_la_fire_multi_step_form_shortcode');


// LA Fire Claims Multi Step Form Shortcode ES
function jfj_la_fire_multi_step_form_shortcode_es($atts)
{
    // Enqueue script and style
	wp_enqueue_style('bootstrap-grid-css');
	wp_enqueue_style('multi-step-form-css');
	wp_enqueue_script('la-fire-claims-multi-step-form-js');

	$theme_url = get_stylesheet_directory_uri();

    // Your shortcode functionality here
    return "
		<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css'>
		<section class='get-started'>
			<div class='container'>
				<div class='row justify-content-center'>
					<div class='col-lg-12 my-5'>
						<form method='post' action='#' id='multiStepForm' class='multi-step-form la-fire-multi-step-form' enctype='multipart/form-data'>

							<input type='hidden' class='current-url' name='referrer'>
							<input type='hidden' class='user-agent' name='user_agent'>
							<input type='hidden' class='display' name='display'>

							<div class='progress mb-5' role='progressbar' aria-label='Progress bar example' aria-valuenow='0'
								aria-valuemin='0' aria-valuemax='100'>
								<div class='progress-bar progress-bar-striped progress-bar-animated'></div>
							</div>

							<!-- Single Step -->
							<div id='step0' class='single-step step'>
								<label for='' class='large-label'>Test Spanish EXPERT LEGAL CONSULTATION FOR FIRE DAMAGE CLAIMS</label>
								<label for='' class='main-label'>Test Spanish Have you been affected by <br> the fire in Los Angeles?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='affected_yes' name='affected' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='affected_yes'>Yes Spanish</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='affected_no' name='affected' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='affected_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step1' class='single-step step'>
								<label for='' class='main-label' id='main_label_last'>Contact Details</label>
								<div class='row g-3'>
									<div class='col-12'>
										<input type='text' class='form-control form-control-lg rounded-1' name='name' id='name' placeholder='Full Name'>
									</div>
									<div class='col-12'>
										<input type='tel' class='form-control form-control-lg rounded-1' name='phone' id='phone' placeholder='Phone Number' required>
									</div>
									<div class='col-12'>
										<input type='email' class='form-control form-control-lg rounded-1' name='email' id='email' placeholder='Email Address (Optional)'>
									</div>
								</div>
								<div class='text-center'>
									<button type='button' class='btn btn-lg btn-continue text-uppercase'>Next</button>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step2' class='single-step step'>
								<label for='' class='main-label'>Has your home, property, or business sustained damage as a result of the fire?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='damage_yes' name='damage' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='damage_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='damage_no' name='damage' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='damage_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step3' class='single-step step'>
								<label for='' class='main-label'>Have you suffered injury or displacement from the fire?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='injured_yes' name='injured' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='injured_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='injured_no' name='injured' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='injured_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step4' class='single-step step'>
								<label for='' class='main-label'>Do you want us to do a FREE additional living expenses (ALE) review as well?</label>
								<div class='row g-3 justify-content-center'>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='living_expense_yes' name='living_expense' value='Yes'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='living_expense_yes'>Yes</label>
										</div>
									</div>
									<div class='col-3'>
										<div class='custom-control custom-radio pl-0'>
											<input type='radio' id='living_expense_no' name='living_expense' value='No'
												class='custom-control-input'>
											<label class='custom-control-label custom-label'
												for='living_expense_no'>No</label>
										</div>
									</div>
								</div>
								<div class='error-message'></div>
							</div>

							<!-- Single Step -->
							<div id='step5' class='single-step step'>
								<div class='row g-3'>
									<div class='col-12'>
										<label for='' class='large-label'>Get an Expedited,<br>FREE Policy Review</label>
										<label for='insurance_policy' class='main-label'>Upload Your Insurance Policy Now!</label>
										<input type='file' class='form-control' name='insurance_policy' id='insurance_policy' accept='.pdf,.jpg,.png,.gif'>
									</div>
									<div class='col-12 text-center'>
										<label for='' class='disclaimer-label'>The sooner we have your information, the faster we can help you secure your insurance payout and address immediate needs, including ALEs like temporary housing and other expenses.</label>
									</div>
									<div class='col-12' style='text-align:center'>
										<div class='form-check d-flex align-items-start'>
											<input class='form-check-input' type='checkbox' name='terms_condition' value='Terms and Condition' id='terms_condition' checked>
											<label class='label-term-condition form-check-label m-0 p-0' for='terms_condition'>
												By submitting this form, I consent to receiving text messages and emails from Farahi Law Firm.
											</label>
										</div>
									</div>
									<div class='col-12' style='text-align:center'>
										<button type='submit' class='btn btn-lg btn-submit'>Submit</button>
									</div>
								</div>
							</div>

							<div id='formStatusMessage' class='mt-3'></div>

							<div class='buttons d-flex gap-3 g-3 align-items-center'>
								<button type='button' class='btn btn-info'><i class='bi bi-chevron-left'></i></button>
								<button type='button' class='btn btn-primary'><i class='bi bi-chevron-right'></i></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</section>
    ";
}
add_shortcode('jfj_la_fire_multi_step_form_es', 'jfj_la_fire_multi_step_form_shortcode_es');

// Visitor Log Start
function get_visitor_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function get_user_agent_info() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform = "Unknown OS";
    $browser = "Unknown Browser";

    if (preg_match('/linux/i', $user_agent)) {
        $os_platform = 'Linux';
    } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
        $os_platform = 'Mac';
    } elseif (preg_match('/windows|win32/i', $user_agent)) {
        $os_platform = 'Windows';
    }

    if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $browser = 'Opera';
    } elseif (preg_match('/Netscape/i', $user_agent)) {
        $browser = 'Netscape';
    }

    return array(
        'os' => $os_platform,
        'browser' => $browser
    );
}

function log_visitor_info() {
	// Get visitor information
	$visitor_ip = get_visitor_ip();
	$visit_time = current_time('mysql');
	$user_agent_info = get_user_agent_info();
	$current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	// Calculate session duration (this requires starting the session at the beginning)
	session_start();
	if (!isset($_SESSION['start_time'])) {
		$_SESSION['start_time'] = time();
	}
	$session_duration = time() - $_SESSION['start_time'];

	// Log the information to a file
	$log_entry = sprintf(
		"%s - IP: %s, OS: %s, Browser: %s, Duration: %s seconds, URL: %s\n",
		$visit_time, $visitor_ip, $user_agent_info['os'], $user_agent_info['browser'], $session_duration, $current_url
	);

	$file = WP_CONTENT_DIR . '/visitor_log.txt';
	if (is_writable($file)) {
		file_put_contents($file, $log_entry, FILE_APPEND);
	} else {
		error_log("Could not write to the visitor log file.");
	}
}
// Hook into the 'wp' action to log the visitor information on specific pages
add_action('wp', 'log_visitor_info');
// Visitor Log End

// amp and noamp URL | Added by Arnold
function add_header_xrobots($headers) {
    if (isset($_GET['amp']) || isset($_GET['nonamp']) || isset($_GET['refPageViewId']) || isset($_GET['s'])){
        $headers['X-Robots-Tag'] = 'noindex';
    }
    return $headers;     
}
add_filter('wp_headers', 'add_header_xrobots');


// Remove Unwanted Tags | Added by Hasan | Verified by Arnold
function remove_unwanted_wp_head_tags() {
    // Remove WordPress version meta tag
    remove_action('wp_head', 'wp_generator');
    
    // Remove RSS feed links
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);

    // Remove XFN profile link
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
    remove_action('wp_head', 'rsd_link'); // Removes RSD link
    
    // Remove JSON and oEmbed links
    remove_action('wp_head', 'rest_output_link_wp_head', 10); // Removes wp-json link
    remove_action('wp_head', 'wp_oembed_add_discovery_links'); // Removes oEmbed discovery
    
    // Remove shortlink
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);
}
add_action('init', 'remove_unwanted_wp_head_tags');



// RAY
function auto_set_image_alt_text($post_ID) {
    $image = get_post($post_ID);

    if ($image->post_type !== 'attachment' || strpos($image->post_mime_type, 'image/') !== 0) {
        return;
    }

    $filename = pathinfo($image->post_title, PATHINFO_FILENAME);
    $alt_text = ucwords(str_replace(array('-', '_'), ' ', $filename));

    update_post_meta($post_ID, '_wp_attachment_image_alt', $alt_text);
}
add_action('add_attachment', 'auto_set_image_alt_text');

// function auto_add_faq_schema_from_elementor() {
//     if (!is_singular()) return;

//     global $post;

//     if (!function_exists('get_post_meta')) return;

//     $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
//     if (empty($elementor_data)) return;

//     $widgets = json_decode($elementor_data, true);
//     if (!is_array($widgets)) return;

//     $faq_items = [];

//     // Recursive function to walk through all Elementor widgets
//     function find_accordion_widgets($elements, &$faq_items) {
//         foreach ($elements as $element) {
//             if (isset($element['elType']) && $element['elType'] === 'widget' && $element['widgetType'] === 'accordion') {
//                 $accordions = $element['settings']['accordion'] ?? [];
//                 foreach ($accordions as $item) {
//                     $question = wp_strip_all_tags($item['title']);
//                     $answer = wp_strip_all_tags($item['content']);
//                     $faq_items[] = [
//                         "@type" => "Question",
//                         "name" => $question,
//                         "acceptedAnswer" => [
//                             "@type" => "Answer",
//                             "text" => $answer
//                         ]
//                     ];
//                 }
//             }

//             // Check for nested widgets
//             if (!empty($element['elements'])) {
//                 find_accordion_widgets($element['elements'], $faq_items);
//             }
//         }
//     }

//     find_accordion_widgets($widgets, $faq_items);

//     // Output FAQ Schema if at least one item is found
//     if (!empty($faq_items)) {
//         $faq_schema = [
//             "@context" => "https://schema.org",
//             "@type" => "FAQPage",
//             "mainEntity" => $faq_items
//         ];

//         echo '<script type="application/ld+json">' . wp_json_encode($faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
//     }
// }
// add_action('wp_footer', 'auto_add_faq_schema_from_elementor');


// RAY
  
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size){
	if (is_front_page() || is_home()) {
	  static $done = false;
	  if (!$done) {
		$attr['loading'] = 'eager';
		$attr['decoding'] = 'async';
		$attr['fetchpriority'] = 'high';
		$done = true;
	  }
	}
	return $attr;
  }, 10, 3);
  
  add_filter('wp_omit_loading_attr_threshold', function($t){
	return (is_front_page() || is_home()) ? 1 : $t;
  });

  	add_filter('sq_jsonld', function($schema) {
		$schema['publisher'] = [
			'@type' => 'Organization',
			'name' => 'Farahi Law Firm',
			'url' => 'https://justinforjustice.com',
			'logo' => [
				'@type' => 'ImageObject',
				'url' => 'https://justinforjustice.com/wp-content/uploads/logo.png'
			]
		];
		return $schema;
	});
  


// function add_blog_post_schema() {
//     if (is_single() && get_post_type() === 'post') {
//         global $post;
//         $author_id = $post->post_author;
//         $schema = [
//             "@context" => "https://schema.org",
//             "@type" => "BlogPosting",
//             "mainEntityOfPage" => [
//                 "@type" => "WebPage",
//                 "@id" => get_permalink($post)
//             ],
//             "headline" => get_the_title($post),
//             "description" => get_the_excerpt($post),
//             "image" => get_the_post_thumbnail_url($post, 'full'),
//             "author" => [
//                 "@type" => "Person",
//                 "name" => get_the_author_meta('display_name', $author_id)
//             ],
//             "publisher" => [
//                 "@type" => "Organization",
//                 "name" => get_bloginfo('name'),
//                 "logo" => [
//                     "@type" => "ImageObject",
//                     "url" => get_theme_mod('custom_logo') 
//                         ? wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full')[0]
//                         : ''
//                 ]
//             ],
//             "datePublished" => get_the_date('c', $post),
//             "dateModified" => get_the_modified_date('c', $post)
//         ];

//         echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>';
//     }
// }
// add_action('wp_head', 'add_blog_post_schema');



// RAY
// add_action('wp_head', 'add_default_website_schema_if_none_exists', 99);

// function add_default_website_schema_if_none_exists() {
//     // Prevent double output by using a static flag
//     static $schema_added = false;

//     // Avoid interfering with SEO plugins that already add schema
//     if ($schema_added || did_action('wpseo_json_ld') || did_action('rank_math/head') || did_action('squirrly_seo_output_schema')) {
//         return;
//     }

//     $schema = [
//         "@context" => "https://schema.org",
//         "@type" => "WebSite",
//         "url" => get_home_url(),
//         "name" => get_bloginfo('name'),
//         "potentialAction" => [
//             "@type" => "SearchAction",
//             "target" => get_home_url('/?s={search_term_string}'),
//             "query-input" => "required name=search_term_string"
//         ]
//     ];

//     echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

//     $schema_added = true;
// }

//END