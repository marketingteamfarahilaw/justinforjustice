<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Author extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_name;
	protected $_phone;
	protected $_job;
	protected $_socials;
	protected $_image;
	protected $_url;

	public function getType() {

		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_personal' ) ) {
			if ( empty( $this->_type ) ) {
				$this->_type = 'Person';
			}
		} else {
			$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );

			if ( isset( $jsonld['Organization']['name'] ) && $jsonld['Organization']['name'] ) {

				if ($this->_name == $jsonld['Organization']['name']){
					$this->_type = 'Organization';
				}
			}

		}

		return $this->_type;
	}

	public function getId() {

		$id = $this->url . "#" . strtolower( substr( md5( $this->name ), 0, 10 ) );

		return apply_filters( 'sqp_jsonld_author_id', $id, $this->post );

	}

	public function getUrl() {

		if ( empty( $this->_url ) ) {
			$this->_url = $this->getAuthor( 'user_url' );
		}

		return apply_filters( 'sqp_jsonld_author_url', $this->_url, $this->post );
	}

	public function getName() {
		if ( empty( $this->_name ) ) {
			$this->_name = $this->getAuthor( 'display_name' );
		}

		return apply_filters( 'sqp_jsonld_author_name', $this->_name, $this->post );
	}

	public function getPhone() {
		return apply_filters( 'sqp_jsonld_author_phone', $this->_phone, $this->post );
	}

	public function getJob() {
		return apply_filters( 'sqp_jsonld_author_job', $this->_job, $this->post );
	}

	public function getSocials() {
		return apply_filters( 'sqp_jsonld_author_socials', $this->_socials, $this->post );
	}

	public function getImage() {
		return apply_filters( 'sqp_jsonld_author_image', $this->_image, $this->post );
	}

	public function getAuthor_schema() {
		return $this->toArray();
	}


	/**
	 * Get the author
	 *
	 * @param string $what
	 *
	 * @return bool|mixed|string
	 */
	protected function getAuthor( $what ) {

		if ( is_author() ) {
			$author = get_userdata( get_query_var( 'author' ) );
		} elseif ( (int) $this->post->post_author > 0 ) {
			if ( $author = get_userdata( (int) $this->post->post_author ) ) {
				$author = $author->data;
			}
		}

		if ( isset( $author->$what ) ) {

			if ( $what == 'user_url' ) {
				return get_author_posts_url( $author->ID, $author->user_nicename );
			}

			return $author->$what;
		}

		return false;
	}


	public function toArray() {

		if ( ! $this->name ) {
			return array();
		}

		$array = array(
			'@type'     => $this->type,
			'@id'       => $this->getId(),
			'url'       => $this->url,
			'name'      => $this->name,
			'jobTitle'  => $this->job,
			'telephone' => $this->phone,
			'sameAs'    => $this->socials,
			'image'     => $this->image,
		);

		return array_filter( $array );
	}

}
