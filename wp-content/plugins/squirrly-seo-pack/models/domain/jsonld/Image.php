<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Image extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_url;
	protected $_image;
	protected $_width;
	protected $_height;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'ImageObject';
		}

		return $this->_type;
	}

	public function getUrl() {

		if ( empty( $this->_url ) ) {
			//get the current post image
			if ( $this->image ) {
				$this->_url = $this->image['url'];
			}
		}

		return $this->_url;
	}

	public function getImage() {

		if ( empty( $this->_image ) ) {
			if ( $this->post->sq->og_media <> '' ) {
				$this->_image = array(
					'url'    => $this->post->sq->og_media,
					'width'  => 500,
					'height' => 500,
				);
			} else {
				$this->_image = $this->getPostImage();
			}
		}

		return apply_filters( 'sqp_jsonld_image', $this->_image );
	}

	public function getHeight() {

		if ( empty( $this->_height ) ) {
			if ( $this->image ) {
				if ( $this->_url == $this->image['url'] ) {
					$this->_height = $this->image['height'];
				}
			}
		}

		return $this->_height;
	}

	public function getWidth() {

		if ( empty( $this->_width ) ) {
			if ( $this->image ) {
				if ( $this->_url == $this->image['url'] ) {
					$this->_width = $this->image['width'];
				}
			}
		}

		return $this->_width;
	}


	public function toArray() {
		$array = array();

		if ( $this->url <> '' ) {
			$array = array(
				'@type'  => $this->type,
				'@id'    => $this->post->url . "#" . strtolower( substr( md5( $this->url ), 0, 10 ) ),
				'url'    => $this->url,
				'width'  => $this->width,
				'height' => $this->height,
			);
		}

		return array_filter( $array );
	}

}
