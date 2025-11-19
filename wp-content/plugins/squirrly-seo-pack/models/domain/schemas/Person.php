<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Person extends SQP_Models_Domain_Schema {

	protected $_name;
	protected $_description;
	protected $_email;
	protected $_address;
	protected $_gender;
	protected $_telephone;
	protected $_jobTitle;
	protected $_sameAs;

	protected $_personal;

	public function __construct( $properties = null ) {
		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );

		if ( isset( $jsonld[ $this->type ] ) ) {
			$this->personal = $jsonld[ $this->type ];
		}

		//get data from Starbox
		if( !empty($this->_post) && $this->_post->id && $starboxJson = get_option('abh_options') ){
			if( $starbox = json_decode($starboxJson, true) ){
				if (isset($starbox['abh_author' . $this->_post->id])){
					if(isset($starbox['abh_author' . $this->_post->id]['abh_title']) &&
					   $starbox['abh_author' . $this->_post->id]['abh_title'] <> ''){
						$this->_jobTitle = $starbox['abh_author' . $this->_post->id]['abh_title'];
					}
					if(isset($starbox['abh_author' . $this->_post->id]['abh_extra_description']) &&
					   $starbox['abh_author' . $this->_post->id]['abh_extra_description'] <> ''){
						$this->_description = $starbox['abh_author' . $this->_post->id]['abh_extra_description'];
					}
				}
			}
		}

		parent::__construct( $properties );

	}

	public function getType() {
		return 'Person';
	}

	public function getName() {
		if ( empty( $this->_name ) ) {
			$this->_name = $this->_personal['name'];
		}

		return $this->_name;
	}

	public function getDescription() {
		if ( empty( $this->_description ) ) {
			$this->_description = $this->_personal['description'];
		}

		return $this->_description;
	}


	/**
	 * @throws Exception
	 */
	public function getAddress() {

		if ( ! empty( array_filter( $this->_address ) ) ) {
			/** @var SQP_Models_Domain_Jsonld_Address $address */
			$address = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Address', $this->_address );

			return $address->toArray();
		}

		return $this->_address;
	}


	public function getTelephone() {

		if ( empty( $this->_telephone ) ) {
			if ( isset( $this->_personal['telephone'] ) && ! empty( $this->_personal['telephone'] ) ) {
				$this->_telephone = $this->_personal['telephone'];
			}
		}

		return $this->_telephone;
	}

	public function getJobTitle() {

		if ( empty( $this->_jobTitle ) ) {
			if ( isset( $this->_personal['jobTitle'] ) && ! empty( $this->_personal['jobTitle'] ) ) {
				$this->_jobTitle = $this->_personal['jobTitle'];
			}
		}

		return $this->_jobTitle;
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
			'url'         => $this->post->url,
			'name'        => $this->name,
			'description' => $this->description,
			'email'       => $this->email,
			'address'     => $this->address,
			'gender'      => $this->gender,
			'telephone'   => $this->telephone,
			'jobTitle'    => $this->jobTitle,
			'sameAs'    => $this->sameAs,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );


	}


}
