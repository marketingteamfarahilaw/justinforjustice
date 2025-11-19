<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Publisher extends SQP_Models_Domain_Schema {

	protected $_type;
	protected $_url;
	protected $_name;
	protected $_description;
	protected $_logo;
	protected $_socials;
	protected $_address;
	protected $_contactPoint;

	protected $_organization;

	public function __construct( $properties = null ) {
		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );

		if ( isset( $jsonld[ $this->type ] ) ) {
			$this->_organization = $jsonld[ $this->type ];
		}

		parent::__construct( $properties );

	}

	public function getId() {

		$id = $this->post->url . "#" . strtolower( $this->type );

		return apply_filters( 'sqp_jsonld_publisher_id', $id, $this->post );

	}

	public function getType() {
		if ( empty( $this->_type ) ) {
			$this->_type = 'Organization';
		}

		return $this->_type;
	}

	public function getUrl() {

		if ( empty( $this->_url ) ) {
			if ( isset( $this->_organization['url'] ) && $this->_organization['url'] <> '' ) {
				$this->_url = $this->_organization['url'];
			} else {
				$this->_url = home_url();
			}
		}

		return apply_filters( 'sqp_jsonld_publisher_url', $this->_url, $this->post );
	}

	public function getName() {
		if ( empty( $this->_name ) ) {
			$this->_name = $this->_organization['name'];
		}

		return apply_filters( 'sqp_jsonld_publisher_name', $this->_name, $this->post );
	}

	public function getDescription() {
		if ( empty( $this->_description ) ) {
			$this->_description = $this->_organization['description'];
		}

		return apply_filters( 'sqp_jsonld_publisher_description', $this->_description, $this->post );
	}

	public function getLogo() {
		if ( empty( $this->_logo ) ) {
			if ( isset( $this->_organization['logo']['url'] ) && $this->_organization['logo']['url'] <> '' ) {
				/** @var SQP_Models_Domain_Jsonld_Image $image */
				$image = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Image', $this->_organization['logo'] );

				$this->_logo = $image->toArray();
			}
		}

		return apply_filters( 'sqp_jsonld_publisher_logo', $this->_logo, $this->post );
	}


	public function getSocials() {
		if ( empty( $this->_socials ) || empty( array_filter( $this->_socials ) ) ) {

			$this->_socials = array();

			$socials = SQP_Classes_Helpers_Tools::getOption( 'socials' );

			//Load the social media
			$jsonld_socials = array();
			if ( isset( $socials['facebook_site'] ) && $socials['facebook_site'] <> '' ) {
				$jsonld_socials[] = $socials['facebook_site'];
			}
			if ( isset( $socials['twitter_site'] ) && $socials['twitter_site'] <> '' ) {
				$jsonld_socials[] = $socials['twitter_site'];
			}
			if ( isset( $socials['instagram_url'] ) && $socials['instagram_url'] <> '' ) {
				$jsonld_socials[] = $socials['instagram_url'];
			}
			if ( isset( $socials['linkedin_url'] ) && $socials['linkedin_url'] <> '' ) {
				$jsonld_socials[] = $socials['linkedin_url'];
			}
			if ( isset( $socials['pinterest_url'] ) && $socials['pinterest_url'] <> '' ) {
				$jsonld_socials[] = $socials['pinterest_url'];
			}
			if ( isset( $socials['youtube_url'] ) && $socials['youtube_url'] <> '' ) {
				$jsonld_socials[] = $socials['youtube_url'];
			}

			if ( ! empty( $jsonld_socials ) ) {
				$this->_socials = $jsonld_socials;
			}
		}

		return apply_filters( 'sqp_jsonld_author_socials', $this->_socials, $this->post );
	}


	public function toArray() {

		if ( ! $this->name ) {
			return array();
		}

		$array = array(
			'@type'        => $this->type,
			'@id'          => $this->getId(),
			'url'          => $this->url,
			'name'         => $this->name,
			'description'  => $this->description,
			'logo'         => $this->logo,
			'address'      => $this->address,
			'contactPoint' => $this->contactPoint,
			'sameAs'       => $this->socials,
		);

		return array_filter( $array );
	}

}
