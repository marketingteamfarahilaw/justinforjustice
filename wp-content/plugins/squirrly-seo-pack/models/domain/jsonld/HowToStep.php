<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_HowToStep extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_name;
	protected $_text;
	protected $_image;

	public function getType() {
		return "HowToStep";
	}

	public function toArray() {

		$array = array(
			'@type' => $this->type,
			'name'  => $this->name,
			'text'  => $this->text,
			'image' => $this->image,
		);

		return array_filter( $array );
	}

}
