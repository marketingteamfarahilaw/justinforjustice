<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

/**
 * The main class for controllers
 */
class SQP_Classes_FrontController {

	/**
	 *
	 *
	 * @var object of the model class
	 */
	public $model;

	/**
	 *
	 *
	 * @var boolean
	 */
	public $flush = true;

	/**
	 *
	 *
	 * @var string Name of the class
	 */
	private $name;

	public function __construct() {
		// Load error class
		SQP_Classes_ObjController::getClass( 'SQP_Classes_Error' );

		/* get the name of the current class */
		$this->name = get_class( $this );

		/* load the model and hooks here for WordPress actions to take effect */
		/* create the model and view instances */
		$model_classname = str_replace( 'Controllers', 'Models', $this->name );
		if ( SQP_Classes_ObjController::getClassPath( $model_classname ) ) {
			$this->model = SQP_Classes_ObjController::getClass( $model_classname );
		}

		//IMPORTANT TO LOAD HOOKS HERE
		/* check if there is a hook defined in the controller clients class */
		SQP_Classes_ObjController::getClass( 'SQP_Classes_HookController' )->setHooks( $this );

		/* Load the Submit Actions Handler */
		SQP_Classes_ObjController::getClass( 'SQP_Classes_ActionController' );
		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' );
		SQP_Classes_ObjController::getClass( 'SQP_Models_Abstract_Domain' );
		SQP_Classes_ObjController::getClass( 'SQP_Models_Abstract_Post' );
		SQP_Classes_ObjController::getClass( 'SQP_Models_Domain_Schema' );

	}

	public function getClass() {
		return $this->name;
	}

	/**
	 * load sequence of classes
	 * Function called usually when the controller is loaded in WP
	 *
	 * @return mixed
	 */
	public function init() {
		$class = SQP_Classes_ObjController::getClassPath( $this->name );

		if ( ! $this->flush ) {
			return $this->get_view( $class['name'] );
		}

		SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( $class['name'] );
		$this->show_view( $class['name'] );

		return false;
	}

	/**
	 * Get the view block
	 *
	 * @param string $view Class name
	 *
	 * @return mixed
	 */
	public function get_view( $view ) {
		return SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->get_view( $view, $this );
	}

	/**
	 * Show the view block
	 *
	 * @param string $view Class name
	 *
	 * @return void
	 */
	public function show_view( $view ) {
		$content = SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->get_view( $view, $this );

		//Support for international languages
		if ( function_exists( 'iconv' ) && SQP_Classes_Helpers_Tools::getOption( 'sqp_non_utf8_support' ) ) {
			if ( strpos( get_bloginfo( "language" ), 'en' ) === false ) {
				$content = iconv( 'UTF-8', 'UTF-8//IGNORE', $content );
			}
		}

		//echo the file from /view directory
		//already escaped in the view
		//Contains HTML output
		echo $content;
	}

	/**
	 * Called as menu callback to show the block
	 */
	public function show() {
		$this->flush = true;

		echo $this->init();
	}

	/**
	 * initialize settings
	 * Called from index
	 *
	 * @return void
	 */
	public function runAdmin() {
		// show the admin menu and post actions
		SQP_Classes_ObjController::getClass( 'SQP_Controllers_Menu' );
		// listen posts, taxonomies and other plugins
		SQP_Classes_ObjController::getClass( 'SQP_Controllers_Backend' );

	}

	/**
	 * Run from frontend
	 */
	public function runFrontend() {
		//Load Frontend only if Squirrly SEO is enabled
		SQP_Classes_ObjController::getClass( 'SQP_Controllers_Frontend' );
	}

	/**
	 * first function call for any class
	 */
	protected function action() {
	}

	/**
	 * This function will load the media in the header for each class
	 *
	 * @return void
	 */
	public function hookHead() {
	}

}
