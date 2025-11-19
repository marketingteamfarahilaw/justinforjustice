<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_ARating extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_ratingValue;
	protected $_reviewCount;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'AggregateRating';
		}

		return $this->_type;
	}

	public function getRatingValue() {

		if ( empty( $this->_ratingValue ) ) {
			$this->_ratingValue = 5;
		}

		return $this->_ratingValue;
	}

	public function getReviewCount() {

		if ( empty( $this->_reviewCount ) ) {
			$this->_reviewCount = 1;
		}

		return (int) $this->_reviewCount;
	}

	public function toArray() {

		$array = array(
			'@type'       => $this->type,
			'ratingValue' => $this->ratingValue,
			'reviewCount' => $this->reviewCount,
		);

		return array_filter( $array );
	}

}
