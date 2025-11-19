<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Organization extends SQP_Models_Domain_Schema {

	protected $_address;
	protected $_local;
	protected $_geo;
	protected $_openingHoursSpecification;
	protected $_contactPoint;
	protected $_priceRange;

	protected $_organization;

	public function __construct( $properties = null ) {
		$jsonld       = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$this->_local = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_local' );

		if ( isset( $jsonld[ $this->type ] ) ) {
			$this->_organization = $jsonld[ $this->type ];
		}

		parent::__construct( $properties );

	}

	public function getType() {
		return 'Organization';
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		/** @var SQP_Models_Domain_Schemas_Publisher $publisher */
		$publisher = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Publisher' );

		//array of publisher
		$array = $publisher->toArray();

		if ( empty( $array['name'] ) ) {
			return array();
		}


		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_auto_jsonld_local' ) ) {
			$array['address']      = $this->address;
			$array['contactPoint'] = $this->contactPoint;

			$array['geo']                       = $this->geo;
			$array['priceRange']                = $this->priceRange;
			$array['openingHoursSpecification'] = $this->openingHoursSpecification;
		}

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
