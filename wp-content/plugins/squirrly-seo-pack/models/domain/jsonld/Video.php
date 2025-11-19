<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Video extends SQP_Models_Abstract_Post {

	protected $_url;
	protected $_image;

	public function getUrl() {

		if ( empty( $this->_url ) ) {

			if ( $video = $this->getPostVideo() ) {
				$this->_url   = $video['url'];
				$this->_image = $video['image'];
			}
		}

		return $this->_url;
	}

	public function getImage() {

		if ( empty( $this->_image ) ) {
			if ( $video = $this->getPostVideo() ) {
				$this->_url   = $video['url'];
				$this->_image = $video['image'];
			}
		}

		return $this->_image;
	}

	public function toArray() {

		$array = array(
			'url'   => $this->url,
			'image' => $this->image,
		);

		return array_filter( $array );
	}

}
