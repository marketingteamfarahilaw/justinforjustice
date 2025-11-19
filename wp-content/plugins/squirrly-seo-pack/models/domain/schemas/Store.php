<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Store extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_organization;
	protected $_local;
	protected $_name;
	protected $_description;
	protected $_telephone;
	protected $_priceRange;
	protected $_address;
	protected $_geo;
	protected $_openingHoursSpecification;
	protected $_image;

	public function __construct( $properties = null ) {
		$jsonld       = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );
		$this->_local = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_local' );

		$type = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ] ) ) {
			$this->_organization = $jsonld[ $type ];
		}

		parent::__construct( $properties );

	}

	public function getType() {
		return 'Store';
	}

	public function getName() {

		if ( empty( $this->_name ) ) {
			if ( isset( $this->_organization['name'] ) && $this->_organization['name'] ) {
				$this->_name = $this->_organization['name'];
			} elseif ( isset( $this->post->sq->title ) ) {
				$this->_name = str_replace( '"', '\"', $this->post->sq->title );
			}
		}

		return $this->_name;
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {
		$array = array(
			'type'                      => $this->type,
			'@id'                       => $this->post->url . '#' . $this->type,
			'url'                       => $this->post->url,
			'name'                      => $this->name,
			'image'                     => $this->image,
			'description'               => $this->description,
			'telephone'                 => $this->telephone,
			'priceRange'                => $this->priceRange,
			'address'                   => $this->address,
			'geo'                       => $this->geo,
			'openingHoursSpecification' => $this->openingHoursSpecification,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
