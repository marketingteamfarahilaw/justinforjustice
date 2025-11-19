<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Newsarticle extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_headline;
	protected $_description;
	protected $_articleSection;
	protected $_datePublished;
	protected $_dateModified;
	protected $_image;
	protected $_author;
	protected $_publisher;
	protected $_keywords;

	public function getDatePublished() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_datePublished );
	}

	public function getDateModified() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_dateModified );
	}

	public function getKeywords() {

		if ( ! isset( $this->_keywords ) ) {
			if ( $this->post->sq->keywords <> '' ) {
				$this->_keywords = $this->post->sq->keywords;
			}
		}

		return $this->_keywords;
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {
		$array = array(
			'type'           => $this->type,
			'@id'            => $this->post->url . '#' . $this->type,
			'url'            => $this->post->url,
			'headline'       => $this->headline,
			'description'    => $this->description,
			'articleSection' => $this->articleSection,
			'dateCreated'    => $this->dateCreated,
			'datePublished'  => $this->datePublished,
			'dateModified'   => $this->dateModified,
			'image'          => $this->image,
			'author'         => $this->author,
			'publisher'      => $this->publisher,
			'keywords'       => $this->keywords,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
