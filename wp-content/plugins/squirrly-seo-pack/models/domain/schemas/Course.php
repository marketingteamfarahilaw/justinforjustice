<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Course extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_image;
	protected $_publisher;
	protected $_review;
	protected $_aggregateRating;
	protected $_offers;
	protected $_hasCourseInstance;
	protected $_provider;

	public function getType() {
		return 'Course';
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		$array = array(
			'type'              => $this->type,
			'@id'               => $this->post->url . '#' . $this->type,
			'url'               => $this->post->url,
			'name'              => $this->name,
			'description'       => $this->description,
			'image'             => $this->image,
			'offers'            => $this->offers,
			'hasCourseInstance' => $this->hasCourseInstance,
			'provider'          => $this->provider,
			'publisher'         => $this->publisher,
			'review'            => $this->review,
			'aggregateRating'   => $this->aggregateRating,
			'hasPart'           => $this->hasPart,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
