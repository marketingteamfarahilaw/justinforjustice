<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Jsonld_Review extends SQP_Models_Abstract_Post {

	protected $_type;
	protected $_datePublished;
	protected $_dateModified;
	protected $_reviewRating;
	protected $_author;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'Review';
		}

		return $this->_type;
	}

	public function getReviewRating() {

		/** @var SQP_Models_Domain_Jsonld_Rating $reviews */
		$reviews = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Rating' );

		if ( ! empty( $this->_reviewRating ) ) {
			$reviews = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Rating', $this->_reviewRating );
		}

		return $reviews->toArray();
	}

	public function getDatePublished() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_datePublished );
	}

	public function getDateModified() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_dateModified );
	}

	public function getAuthor() {

		/** @var SQP_Models_Domain_Jsonld_Author $author */
		$author = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Author' );

		if ( ! empty( $this->_author ) ) {
			$author = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Author', $this->_author );
		}

		return $author->toArray();
	}

	public function toArray() {
		$array = array();

		if ( isset( $this->reviewRating['ratingValue'] ) && $this->reviewRating['ratingValue'] > 0 ) {
			$array = array(
				'@type'         => $this->type,
				'@id'           => $this->post->url . "#" . strtolower( $this->type ),
				'datePublished' => $this->datePublished,
				'dateModified'  => $this->dateModified,
				'reviewRating'  => $this->reviewRating,
				'author'        => $this->author,
			);
		}

		return array_filter( $array );
	}

}
