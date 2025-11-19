<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Rating extends SQP_Models_Abstract_Post {

	protected $_ratingValue;
	protected $_worstRating;
	protected $_bestRating;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'Rating';
		}

		return $this->_type;
	}

	public function getRatingValue() {

		if ( empty( $this->_ratingValue ) ) {
			$this->_ratingValue = 5;
		}

		return $this->_ratingValue;
	}

	public function getWorstRating() {

		if ( empty( $this->_worstRating ) ) {
			$this->_worstRating = 1;
		}

		return (int) $this->_worstRating;
	}

	public function getBestRating() {

		if ( empty( $this->_bestRating ) ) {
			$this->_bestRating = 5;
		}

		return (int) $this->_bestRating;
	}

	public function toArray() {

		$array = array(
			'@type'       => $this->type,
			'ratingValue' => $this->ratingValue,
			'worstRating' => $this->worstRating,
			'bestRating'  => $this->bestRating,
		);

		return array_filter( $array );
	}

}
