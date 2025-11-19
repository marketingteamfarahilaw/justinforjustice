<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Event extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_eventStatus;
	protected $_eventAttendanceMode;
	protected $_virtualLocation;
	protected $_location;
	protected $_image;
	protected $_performer;
	protected $_organizer;
	protected $_startDate;
	protected $_endDate;
	protected $_offers;
	protected $_publisher;
	protected $_review;
	protected $_aggregateRating;

	public function __construct( $properties = null ) {

		$this->processCompatibility();

		parent::__construct( $properties );
	}

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'Event';
		}

		return $this->_type;
	}

	public function getStartDate() {
		$this->_startDate = apply_filters( 'sqp_jsonld_startDate', $this->_startDate );

		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_startDate );
	}

	public function getEndDate() {
		$this->_endDate = apply_filters( 'sqp_jsonld_endDate', $this->_endDate );

		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_endDate );
	}

	public function getEventAttendanceMode() {

		if ( ! empty( $this->_eventAttendanceMode ) ) {
			if ( strpos( $this->_eventAttendanceMode, 'schema.org' ) === false ) {
				$this->_eventAttendanceMode = 'https://schema.org/' . $this->_eventAttendanceMode;
			}
		}

		return $this->_eventAttendanceMode;
	}

	public function getLocation() {

		if ( ! empty( $this->eventAttendanceMode ) ) {

			switch ( $this->_eventAttendanceMode ) {
				case 'https://schema.org/OfflineEventAttendanceMode':
					return $this->_location;
				case 'https://schema.org/OnlineEventAttendanceMode':
					return $this->virtualLocation;
				case 'https://schema.org/MixedEventAttendanceMode':
					return array(
						$this->_location,
						$this->virtualLocation
					);
			}
		}

		return $this->_location;
	}

	public function processCompatibility() {

		//The Events Calendar compatibility
		if ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'the-events-calendar/the-events-calendar.php' ) ) {

			add_filter( 'sqp_jsonld_startDate', function( $date ) {

				if ( empty( $date ) && isset( $this->post->ID ) ) {
					if ( $value = get_post_meta( (int) $this->post->ID, '_EventStartDate', true ) ) {
						return $value;
					}
				}

				return $date;
			} );

			add_filter( 'sqp_jsonld_endDate', function( $date ) {

				if ( empty( $date ) && isset( $this->post->ID ) ) {
					if ( $value = get_post_meta( (int) $this->post->ID, '_EventEndDate', true ) ) {
						return $value;
					}
				}

				return $date;
			} );

		}

	}


	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		$array = array(
			'type'                => $this->type,
			'@id'                 => $this->post->url . '#' . $this->type,
			'url'                 => $this->post->url,
			'name'                => $this->name,
			'description'         => $this->description,
			'eventStatus'         => $this->eventStatus,
			'eventAttendanceMode' => $this->eventAttendanceMode,
			'location'            => $this->location,
			'image'               => $this->image,
			'performer'           => $this->performer,
			'organizer'           => $this->organizer,
			'startDate'           => $this->startDate,
			'endDate'             => $this->endDate,
			'offers'              => $this->offers,
			'publisher'           => $this->publisher,
			'review'              => $this->review,
			'aggregateRating'     => $this->aggregateRating,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
