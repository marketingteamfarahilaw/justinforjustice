<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Publisher extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_url;

	protected $_organization;

	public function __construct( $properties = null ) {
		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );

		if ( isset( $jsonld[ $this->type ] ) ) {
			$this->_organization = $jsonld[ $this->type ];
		}

		parent::__construct( $properties );

	}

	public function getId() {

		$id = $this->url . "#" . strtolower( $this->type );

		return apply_filters( 'sqp_jsonld_publisher_id', $id, $this->post );

	}

	public function getType() {
		if ( empty( $this->_type ) ) {
			$this->_type = 'Organization';
		}

		return $this->_type;
	}

	public function getUrl() {

		if ( empty( $this->_url ) ) {
			$this->_url = home_url();
		}

		return apply_filters( 'sqp_jsonld_publisher_url', $this->_url, $this->post );
	}


	public function toArray() {

		if ( ! $this->url ) {
			return array();
		}

		$array = array(
			'@id' => $this->getId()
		);

		return array_filter( $array );
	}

}
