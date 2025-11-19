<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Book extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_image;
	protected $_author;
	protected $_publisher;
	protected $_review;
	protected $_aggregateRating;
	protected $_workExample;
	protected $_sameAs;
	protected $_hasPart;

	public function getType() {
		return 'Book';
	}

	public function getHasPart() {

		if ( ! empty( $this->_hasPart ) ) {
			if ( isset( $this->_hasPart['bookFormat'] ) && strpos( $this->_hasPart['bookFormat'], 'schema.org' ) === false ) {
				$this->_hasPart['bookFormat'] = 'https://schema.org/' . $this->_hasPart['bookFormat'];
			}
		}

		return $this->_hasPart;
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
			'image'           => $this->image,
			'author'          => $this->author,
			'publisher'       => $this->publisher,
			'review'          => $this->review,
			'aggregateRating' => $this->aggregateRating,
			'sameAs'          => $this->sameAs,
			'hasPart'         => $this->hasPart,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
