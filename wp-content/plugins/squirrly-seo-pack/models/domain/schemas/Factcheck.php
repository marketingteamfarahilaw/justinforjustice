<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Factcheck extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_claimReviewed;
	protected $_reviewRating;
	protected $_datePublished;
	protected $_itemReviewed;
	protected $_image;
	protected $_author;
	protected $_publisher;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'ClaimReview';
		}

		return $this->_type;
	}

	public function toArray() {

		$array = array(
			'type'          => $this->type,
			'@id'           => $this->post->url . "#" . strtolower( $this->type ),
			'url'           => $this->url,
			'claimReviewed' => $this->claimReviewed,
			'reviewRating'  => $this->reviewRating,
			'datePublished' => $this->datePublished,
			'itemReviewed'  => $this->itemReviewed,
			'image'         => $this->image,
			'author'        => $this->author,
			'publisher'     => $this->publisher,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );
	}


}
