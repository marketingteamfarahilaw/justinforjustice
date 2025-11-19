<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Address extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_streetAddress;
	protected $_addressLocality;
	protected $_addressRegion;
	protected $_postalCode;
	protected $_addressCountry;


	public function getType() {

		if ( ! isset( $this->_type ) ) {
			$this->_type = 'PostalAddress';
		}

		return $this->_type;
	}

	public function toArray() {
		$array = array(
			'@type'           => $this->type,
			'streetAddress'   => $this->streetAddress,
			'addressLocality' => $this->addressLocality,
			'addressRegion'   => $this->addressRegion,
			'postalCode'      => $this->postalCode,
			'addressCountry'  => $this->addressCountry,
		);

		return array_filter( $array );
	}

}
