<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Item extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_url;
	protected $_name;
	protected $_image;

	public function toArray() {

		$array = array(
			'@type' => $this->type,
			'id'   => $this->url . "#" . $this->type,
			'url'   => $this->url,
			'name'  => $this->name,
			'image' => $this->image,
		);

		return array_filter( $array );
	}

}
