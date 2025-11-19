<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Howto extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_totalTime;
	protected $_estimatedCost;
	protected $_material;
	protected $_supply;
	protected $_tool;
	protected $_step;
	protected $_image;
	protected $_author;

	public function getType() {
		return 'HowTo';
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {
		$array = array(
			'type'          => $this->type,
			'@id'           => $this->post->url . '#' . $this->type,
			'url'           => $this->post->url,
			'name'          => $this->name,
			'description'   => $this->description,
			'totalTime'     => $this->totalTime,
			'estimatedCost' => $this->estimatedCost,
			'material'      => $this->material,
			'supply'        => $this->supply,
			'tool'          => $this->tool,
			'step'          => $this->step,
			'image'         => $this->image,
			'author'        => $this->author,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}

}
