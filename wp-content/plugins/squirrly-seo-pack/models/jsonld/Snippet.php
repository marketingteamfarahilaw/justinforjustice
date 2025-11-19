<?php
defined('ABSPATH') || die('Cheatin\' uh?');

class SQP_Models_Jsonld_Snippet
{
	public $view;

	/**
	 * Hook the Squirrly Snippet
	 * @param $file
	 * @param $block
	 *
	 * @return mixed|string
	 */
	public function loadSnippetView($file, $block){

		// Image Search assets.
		if ( 'Snippet/Snippet' === $block && !SQP_Classes_Helpers_Tools::isAjax()) {
			$this->loadJsonLdScripts();
		}

		return $file;

	}

	/**
	 * Hook JsonLD Snippet Content from Advanced Pack
	 * @param $view
	 *
	 * @return void
	 */
	public function loadSnippetJsonLd($content, $view){
		return SQP_Classes_ObjController::getClass('SQP_Classes_DisplayController')->get_view('Jsonld/Snippet', $view);
	}

	/**
	 * Load the scripts from Advanced Pack
	 * @return void
	 */
	public function loadJsonLdScripts(){

		$data = array(
			'wrap' => '.sq_blocksnippet'
		);

		SQP_Classes_ObjController::getClass('SQP_Classes_DisplayController')->loadMedia('jquery/datetimepicker');

		if (!SQP_Classes_Helpers_Tools::getOption('sq_seoexpert')) {
			SQP_Classes_ObjController::getClass('SQP_Classes_DisplayController')->loadMedia('beginner');
		}

		$handle = SQP_Classes_ObjController::getClass( 'SQP_Classes_DisplayController' )->loadMedia( 'jsonld/schemas', array(
			'dependencies' => array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'clipboard', 'wp-i18n')
		) );

		wp_localize_script( $handle, 'sqp_jsonld', $data );
	}

	/**
	 * Recursively adds a span tag to all keys and values of an array.
	 *
	 * @param array $arr The array to modify.
	 *
	 * @return array The modified array with all keys and values wrapped in span tags.
	 */
	public function addSpanTagsRecursive($arr) {
		$newArr = array();
		foreach ($arr as $key => $value) {
			if(is_string($key)){
				$key = "<span class='key'>" . $key . "</span>" ;
			}
			if (is_array($value)) {
				$newArr[ $key ] = $this->addSpanTagsRecursive($value);
			} else {
				$newArr[ $key ] = "<span class='string'>" . $value . "</span>";
			}
		}
		return $newArr;
	}




}