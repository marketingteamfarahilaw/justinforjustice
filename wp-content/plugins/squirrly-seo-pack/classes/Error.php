<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Classes_Error extends SQP_Classes_FrontController {

	/**
	 * The list of errors
	 * @var array
	 */
	private static $errors = array();

	public function __construct() {
		parent::__construct();

		add_action( 'sqp_notices', array( 'SQP_Classes_Error', 'hookNotices' ) );
	}

	/**
	 * Get the error message
	 *
	 * @return int
	 */
	public static function getError() {
		if ( count( self::$errors ) > 0 ) {
			return self::$errors[0]['text'];
		}

		return false;
	}

	/**
	 * Clear all the Errors from Squirrly SEO
	 */
	public static function clearErrors() {
		self::$errors = array();
	}

	/**
	 * Show the error in WordPress
	 *
	 * @param string $error
	 * @param string $type
	 *
	 * @return void
	 */
	public static function setError( $error = '', $type = 'error' ) {
		self::$errors[] = array(
			'type' => $type,
			'text' => $error
		);
	}

	/**
	 * Set a success message
	 *
	 * @param string $message
	 * @param string $id
	 */
	public static function setMessage( $message = '' ) {
		self::$errors[] = array(
			'type' => 'success',
			'text' => $message
		);
	}

	/**
	 * Check if there is a Squirrly Error triggered
	 *
	 * @return bool
	 */
	public static function isError() {
		if ( ! empty( self::$errors ) ) {
			foreach ( self::$errors as $error ) {
				if ( $error['type'] <> 'success' ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * This hook will show the error in WP header
	 */
	public static function hookNotices() {
		if ( is_array( self::$errors ) && ! empty( self::$errors ) ) {
			foreach ( self::$errors as $error ) {

				switch ( $error['type'] ) {
					case 'notice':
					case 'success':
						self::showNotices( $error['text'] );
						break;

					default:
						self::showError( $error['text'], $error['type'] );
				}
			}
		}
	}

	/**
	 * Show the notices to WP
	 *
	 * @param  $message
	 * @param string $type
	 *
	 * @return void
	 */
	public static function showNotices( $message, $type = 'notice' ) {
		if ( file_exists( _SQP_THEME_DIR_ . 'Notices.php' ) ) {
			include _SQP_THEME_DIR_ . 'Notices.php';
		}
	}

	/**
	 * Show the notices to WP
	 *
	 * @param string $message
	 * @param string $id
	 * @param string $type
	 *
	 * return void
	 */
	public static function showError( $message, $id = '', $type = 'error' ) {
		if ( file_exists( _SQP_THEME_DIR_ . 'Notices.php' ) ) {
			include _SQP_THEME_DIR_ . 'Notices.php';
		}
	}


}
