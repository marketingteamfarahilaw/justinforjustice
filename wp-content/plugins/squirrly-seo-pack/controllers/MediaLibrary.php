<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_MediaLibrary extends SQP_Classes_FrontController {

	/**
	 * API URL which is used to get the response from Pixabay.
	 *
	 * @var (String) URL
	 */
	public $pixabay_url;

	/**
	 * API Key which is used to get the response from Pixabay.
	 *
	 * @var (String) URL
	 */
	public $pixabay_api_key;

	/** @var array of rich snippet schemas */
	public $schemas = array();

	public function __construct() {
		parent::__construct();

		$this->pixabay_url     = 'https://pixabay.com/api/';
		$this->pixabay_api_key = '2727911-c4d7c1031949c7e0411d7e81e';
	}


	/**
	 * Called when Post action is triggered
	 *
	 * @return void
	 */
	public function action() {

		parent::action();

		if ( ! SQP_Classes_Helpers_Tools::userCan( 'sq_manage_snippet' ) ) {
			return;
		}

		switch ( SQP_Classes_Helpers_Tools::getValue( 'action' ) ) {

			case 'sqp_medialibrary_create_image':

				if ( ! current_user_can( 'upload_files' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'squirrly-seo-pack' ) );
				}

				$url      = isset( $_POST['url'] ) ? sanitize_url( $_POST['url'] ) : false;
				$name     = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : false;
				$photo_id = isset( $_POST['id'] ) ? absint( sanitize_key( $_POST['id'] ) ) : 0;

				if ( false === $url ) {
					wp_send_json_error( __( 'Need to send URL of the image to be downloaded', 'squirrly-seo-pack' ) );
				}

				$result = array();

				$name  = preg_replace( '/\.[^.]+$/', '', $name ) . '-' . $photo_id . '.jpg';
				$image = $this->create_image_from_url( $url, $name, $photo_id );

				if ( is_wp_error( $image ) ) {
					wp_send_json_error( $image );
				}

				if ( 0 !== $image ) {
					$result['attachmentData'] = wp_prepare_attachment_for_js( $image );
					if ( did_action( 'elementor/loaded' ) ) {
						$result['data'] = SQP_Classes_ObjController::getClass( 'SQP_Models_Images' )->getElementorData( $image );
					}
					if ( 0 === $photo_id ) {
						update_post_meta( $image, '_sq_sites_imported_post', true );
					}
				} else {
					wp_send_json_error( __( 'Could not download the image.', 'squirrly-seo-pack' ) );
				}

				// Save downloaded image reference to an option.
				if ( 0 !== $photo_id ) {
					$saved_images = get_option( SQP_IMAGES, array() );

					if ( empty( $saved_images ) || false === $saved_images ) {
						$saved_images = array();
					}

					$saved_images[] = $photo_id;
					update_option( SQP_IMAGES, $saved_images, 'no' );
				}

				$result['updated-saved-images'] = get_option( SQP_IMAGES, array() );

				wp_send_json_success( $result );

				exit;

			case 'sqp_medialibrary_search':

				if ( ! current_user_can( 'upload_files' ) ) {
					wp_send_json_error( __( 'You are not allowed to perform this action', 'squirrly-seo-pack' ) );
				}

				$params = isset( $_POST['params'] ) ? array_map( 'sanitize_text_field', $_POST['params'] ) : array();

				$params['key'] = $this->pixabay_api_key;

				$api_url = add_query_arg( $params, $this->pixabay_url );

				$response = wp_remote_get( $api_url );

				if ( is_wp_error( $response ) ) {
					wp_send_json_error( wp_remote_retrieve_body( $response ) );
				}

				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				wp_send_json_success( $data );

				exit;

		}
	}

	/**
	 * Create the image and return the new media upload id.
	 *
	 * @param String $url URL to pixabay image.
	 * @param String $name Name to pixabay image.
	 * @param String $photo_id Photo ID to pixabay image.
	 *
	 * @see http://codex.wordpress.org/Function_Reference/wp_insert_attachment#Example
	 */
	public function create_image_from_url( $url, $name, $photo_id ) {
		$file_array         = array();
		$file_array['name'] = wp_basename( $name );

		// Download file to temp location.
		$file_array['tmp_name'] = download_url( $url );

		// If error storing temporarily, return the error.
		if ( is_wp_error( $file_array['tmp_name'] ) ) {
			return $file_array;
		}

		// Do the validation and storage stuff.
		$id = media_handle_sideload( $file_array, 0, null );

		// If error storing permanently, unlink.
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink -- Deleting the file from temp location.

			return $id;
		}

		// Store the original attachment source in meta.
		add_post_meta( $id, '_source_url', $url );

		update_post_meta( $id, 'sq-images', $photo_id );
		update_post_meta( $id, '_wp_attachment_image_alt', $name );

		return $id;
	}

}