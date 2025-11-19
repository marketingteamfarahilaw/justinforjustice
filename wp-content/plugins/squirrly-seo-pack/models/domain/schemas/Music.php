<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Music extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_dateCreated;
	protected $_image;
	protected $_author;
	protected $_publisher;

	public function getDateCreated() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_dateCreated );
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		$array = array(
			'type'        => $this->type,
			'@id'         => $this->post->url . '#' . $this->type,
			'url'         => $this->url,
			'name'        => $this->name,
			'description' => $this->description,
			'dateCreated' => $this->dateCreated,
			'image'       => $this->image,
			'author'      => $this->author,
			'publisher'   => $this->publisher,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
