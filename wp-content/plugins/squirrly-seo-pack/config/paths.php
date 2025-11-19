<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

$currentDir = dirname( __FILE__ );

define( '_SQP_MENU_NAME_', 'Squirrly SEO - Advanced Pack' );
define( '_SQP_NAME_', 'squirrly-seo-pack' );
define( '_SQP_NAMESPACE_', 'SQP' );

/* Directories */
define( '_SQP_ROOT_DIR_', realpath( dirname( $currentDir ) ) . '/' );
define( '_SQP_CLASSES_DIR_', _SQP_ROOT_DIR_ . 'classes/' );
define( '_SQP_CONTROLLER_DIR_', _SQP_ROOT_DIR_ . 'controllers/' );
define( '_SQP_MODEL_DIR_', _SQP_ROOT_DIR_ . 'models/' );
define( '_SQP_THEME_DIR_', _SQP_ROOT_DIR_ . 'view/' );
define( '_SQP_ASSETS_DIR_', _SQP_THEME_DIR_ . 'assets/' );

/* URLS */
define( '_SQP_URL_', rtrim( plugins_url( '', $currentDir ), '/' ) . '/' );
define( '_SQP_VIEW_URL_', _SQP_URL_ . 'view/' );
define( '_SQP_ASSETS_URL_', _SQP_VIEW_URL_ . 'assets/' );
define( '_SQP_ASSETS_RELATIVE_URL_', ltrim( parse_url( _SQP_ASSETS_URL_, PHP_URL_PATH ), '/' ) );
define( '_SQP_THEME_URL_', _SQP_VIEW_URL_ . 'Themes/' );


