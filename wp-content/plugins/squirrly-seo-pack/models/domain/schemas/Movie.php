<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Movie extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_dateCreated;
	protected $_image;
	protected $_director;
	protected $_publisher;
	protected $_review;
	protected $_aggregateRating;

	public function getType() {
		return 'Movie';
	}

	public function getDirector() {

		/** @var SQP_Models_Domain_Jsonld_Author $author */
		$author = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Author' );

		if ( isset( $this->_director ) ) {
			$author = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Author', $this->_director );
		}

		return $author->toArray();
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
			'url'             => $this->post->url,
			'name'            => $this->name,
			'description'     => $this->description,
			'dateCreated'     => $this->dateCreated,
			'image'           => $this->image,
			'director'        => $this->director,
			'publisher'       => $this->publisher,
			'review'          => $this->review,
			'aggregateRating' => $this->aggregateRating,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
