<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( ! defined( '_SQP_NONCE_ID_' ) ) {
	if ( defined( '_SQ_NONCE_ID_' ) ) {
		define( '_SQP_NONCE_ID_', _SQ_NONCE_ID_ );
	} elseif ( defined( 'NONCE_KEY' ) ) {
		define( '_SQP_NONCE_ID_', NONCE_KEY );
	} else {
		define( '_SQP_NONCE_ID_', md5( gmdate( 'Y-d' ) ) );
	}
}

if ( ! defined( 'SQP_DEBUG' ) ) {
	if ( defined( 'SQ_DEBUG' ) ) {
		define( 'SQP_DEBUG', SQ_DEBUG );
	} else {
		define( 'SQP_DEBUG', 0 );
	}
}


/* No path file? error ... */
require_once dirname( __FILE__ ) . '/paths.php';

/* Define the record name in the Option and UserMeta tables */
defined( 'SQP_OPTION' ) || define( 'SQP_OPTION', 'sq_options' );
defined( 'SQP_IMAGES' ) || define( 'SQP_IMAGES', 'sq-sites-saved-images' );



