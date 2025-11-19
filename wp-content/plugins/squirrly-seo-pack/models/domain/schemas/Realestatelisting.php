<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Realestatelisting extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_offers;
	protected $_review;
	protected $_aggregateRating;
	protected $_image;
	protected $_publisher;

	public function getType() {
		return 'RealEstateListing';
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		$array = array(
			'type'            => $this->type,
			'@id'             => $this->post->url . '#' . $this->type,
			'url'             => $this->url,
			'name'            => $this->name,
			'description'     => $this->description,
			'offers'          => $this->offers,
			'review'          => $this->review,
			'aggregateRating' => $this->aggregateRating,
			'image'           => $this->image,
			'publisher'       => $this->publisher,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
