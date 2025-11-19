<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Aggregaterating extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_itemReviewed;
	protected $_ratingValue;
	protected $_bestRating;
	protected $_worstRating;
	protected $_ratingCount;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'AggregateRating';
		}

		return $this->_type;
	}

	public function toArray() {
		$array = array();

		if ( ! empty( $this->ratingValue ) ) {
			$array = array(
				'type'         => $this->type,
				'@id'          => $this->post->url . "#" . strtolower( $this->type ),
				'ratingValue'  => $this->ratingValue,
				'bestRating'   => $this->bestRating,
				'worstRating'  => $this->worstRating,
				'ratingCount'  => $this->ratingCount,
				'itemReviewed' => $this->itemReviewed,
			);
		}

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );
	}


}
