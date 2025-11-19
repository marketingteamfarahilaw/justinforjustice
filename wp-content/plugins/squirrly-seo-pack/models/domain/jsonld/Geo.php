<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Geo extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_latitude;
	protected $_longitude;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'GeoCoordinates';
		}

		return $this->_type;
	}

	public function toArray() {
		$array = array(
			'@type'     => $this->type,
			'latitude'  => $this->latitude,
			'longitude' => $this->longitude
		);

		return array_filter( $array );
	}

}
