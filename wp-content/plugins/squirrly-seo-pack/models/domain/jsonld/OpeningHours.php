<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_OpeningHours extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_dayOfWeek;
	protected $_opens;
	protected $_closes;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'OpeningHoursSpecification';
		}

		return $this->_type;
	}

	public function getOpens() {

		if ( empty( $this->_opens ) ) {
			$this->_opens = '09:00 AM';
		}

		return $this->_opens;
	}

	public function getCloses() {

		if ( empty( $this->_closes ) ) {
			$this->_closes = '05:00 PM';
		}

		return $this->_closes;
	}

	public function toArray() {
		$array = array(
			'@type'     => $this->type,
			'dayOfWeek' => $this->dayOfWeek,
			'opens'     => $this->opens,
			'closes'    => $this->closes
		);

		return array_filter( $array );
	}

}
