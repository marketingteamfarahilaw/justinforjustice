<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Video extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_uploadDate;
	protected $_contentUrl;
	protected $_embedUrl;
	protected $_duration;
	protected $_interactionCount;
	protected $_thumbnailUrl;
	protected $_author;
	protected $_sameAs;
	protected $_hasPart;

	public function getType() {
		return 'VideoObject';
	}

	public function getUploadDate() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_uploadDate );
	}

	public function getContentUrl() {
		if ( empty( $this->_contentUrl ) ) {
			/** @var SQP_Models_Domain_Jsonld_Video $videoDomain */
			$videoDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Video' );

			$this->_contentUrl = $videoDomain->getUrl();
		}

		return $this->_contentUrl;
	}

	public function getThumbnailUrl() {
		if ( empty( $this->_thumbnailUrl ) ) {
			/** @var SQP_Models_Domain_Jsonld_Video $videoDomain */
			$videoDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Video' );

			$this->_thumbnailUrl = $videoDomain->getImage();
		}

		return $this->_thumbnailUrl;
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {
		$array = array();

		if ( $this->_embedUrl <> '' && $this->_contentUrl == '' ) {
			$this->_contentUrl = $this->_embedUrl;
		}

		if ( $this->contentUrl <> '' ) {
			$array = array(
				'type'             => $this->type,
				'@id'              => $this->post->url . '#' . $this->type,
				'url'              => $this->post->url,
				'name'             => $this->name,
				'description'      => $this->description,
				'uploadDate'       => $this->uploadDate,
				'contentUrl'       => $this->contentUrl,
				'embedUrl'         => $this->embedUrl,
				'duration'         => $this->duration,
				'interactionCount' => $this->interactionCount,
				'thumbnailUrl'     => $this->thumbnailUrl,
				'author'           => $this->author,
				'sameAs'           => $this->sameAs,
				'hasPart'          => $this->hasPart,
			);
		}

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
