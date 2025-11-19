<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Jobposting extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_title;
	protected $_description;
	protected $_baseSalary;
	protected $_datePosted;
	protected $_validThrough;
	protected $_employmentType;
	protected $_hiringOrganization;
	protected $_identifier;
	protected $_jobLocation;
	protected $_unpublish;
	protected $_image;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'JobPosting';
		}

		return $this->_type;
	}

	public function getDatePosted() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_datePosted );
	}

	public function getValidThrough() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_validThrough );
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		if ( $this->unpublish == 'on' && $this->validThrough && strtotime( $this->validThrough ) < time() ) {
			return array();
		}

		$array = array(
			'type'               => $this->type,
			'@id'                => $this->post->url . '#' . $this->type,
			'url'                => $this->post->url,
			'title'              => $this->title,
			'description'        => $this->description,
			'baseSalary'         => $this->baseSalary,
			'datePosted'         => $this->datePosted,
			'validThrough'       => $this->validThrough,
			'employmentType'     => $this->employmentType,
			'hiringOrganization' => $this->hiringOrganization,
			'identifier'         => $this->identifier,
			'jobLocation'        => $this->jobLocation,
			'image'              => $this->image,

		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
