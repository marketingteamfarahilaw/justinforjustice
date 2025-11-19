<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schema extends SQP_Models_Abstract_Post {

	protected $_id;
	protected $_type;

	public function _getIgnoredProperties() {
		return array( 'post_id', 'term_id', 'taxonomy', 'post_type', 'post' );
	}

	public function getDatePublished() {
		$this->_datePublished = apply_filters( 'sqp_jsonld_datePublished', $this->_datePublished );

		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_datePublished );
	}

	public function getDateCreated() {
		$this->_dateCreated = apply_filters( 'sqp_jsonld_dateCreated', $this->_dateCreated );

		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_dateCreated );
	}

	public function getDateModified() {
		$this->_dateModified = apply_filters( 'sqp_jsonld_dateModified', $this->_dateModified );

		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_dateModified );
	}

	public function getUploadDate() {
		$this->_uploadDate = apply_filters( 'sqp_jsonld_uploadDate', $this->_uploadDate );

		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_uploadDate );
	}

	public function getTitle() {

		if ( empty( $this->_title ) ) {
			if ( isset( $this->post->sq->title ) ) {
				$this->_title = $this->cleanText( $this->truncate( $this->post->sq->title, 0, $this->post->sq->jsonld_title_maxlength ) );
			}
		}

		return $this->_title;
	}

	public function getName() {

		if ( empty( $this->_name ) ) {
			if ( isset( $this->post->sq->title ) ) {
				$this->_name = $this->cleanText( $this->truncate( $this->post->sq->title, 0, $this->post->sq->jsonld_title_maxlength ) );
			}
		}

		return $this->_name;
	}

	public function getDescription() {

		if ( empty( $this->_description ) ) {
			if ( isset( $this->post->sq->description ) ) {
				$this->_description = $this->cleanText( $this->truncate( $this->post->sq->description, 0, $this->post->sq->jsonld_description_maxlength ) );
			}
		}

		return $this->_description;
	}

	public function getImage() {

		/** @var SQP_Models_Domain_Jsonld_Image $image */
		$image = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Image' );

		if ( ! empty( $this->_image ) ) {
			$image = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Image', $this->_image );
		}

		return $image->toArray();
	}

	public function getAuthor() {

		/** @var SQP_Models_Domain_Jsonld_Author $author */
		$author = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Author' );

		if ( ! empty( $this->_author ) ) {
			$author = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Author', $this->_author );
		}

		return $author->toArray();
	}

	public function getAddress() {

		if ( ! isset( $this->_address ) || empty( array_filter( $this->_address ) ) || count( array_filter( $this->_address ) ) == 1 ) {
			if ( isset( $this->_organization['address'] ) && ! empty( $this->_organization['address'] ) ) {
				$this->_address = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Address', $this->_organization['address'] )->toArray();
			}
		}

		return $this->_address;
	}

	public function getGeo() {

		if ( ! isset( $this->_geo ) || empty( array_filter( $this->_geo ) ) || count( array_filter( $this->_geo ) ) == 1 ) {
			if ( isset( $this->_organization['place']['geo'] ) && ! empty( $this->_organization['place']['geo'] ) ) {
				$this->_geo = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Geo', $this->_organization['place']['geo'] )->toArray();
			}
		}

		return $this->_geo;
	}

	public function getTelephone() {

		if ( empty( $this->_telephone ) ) {
			if ( isset( $this->_organization['contactPoint']['telephone'] ) && ! empty( $this->_organization['contactPoint']['telephone'] ) ) {
				$this->_telephone = $this->_organization['contactPoint']['telephone'];
			}
		}

		return $this->_telephone;
	}

	public function getContactPoint() {

		if ( empty( $this->_contactPoint ) ) {
			if ( isset( $this->_organization['contactPoint'] ) && ! empty( $this->_organization['contactPoint'] ) ) {
				$this->_contactPoint = $this->_organization['contactPoint'];
			}
		}

		return $this->_contactPoint;
	}

	public function getOpeningHoursSpecification() {

		if ( ! isset( $this->_openingHoursSpecification ) || empty( array_filter( $this->_openingHoursSpecification ) ) ) {
			if ( isset( $this->_local['openingHoursSpecification'] ) && ! empty( $this->_local['openingHoursSpecification'] ) ) {
				foreach ( $this->_local['openingHoursSpecification'] as $index => $row ) {
					$this->_openingHoursSpecification[] = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_OpeningHours', $row )->toArray();
				}
			}
		}

		return $this->_openingHoursSpecification;
	}

	public function getPriceRange() {

		if ( empty( $this->_priceRange ) ) {
			if ( isset( $this->_local['priceRange'] ) && ! empty( $this->_local['priceRange'] ) ) {
				$this->_priceRange = $this->_local['priceRange'];
			}
		}

		return $this->_priceRange;
	}

	public function getPublisher() {

		/** @var SQP_Models_Domain_Jsonld_Publisher $publisher */
		$publisher = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Publisher' );

		/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
		$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld' );

		//Check publisher by post type
		$post_types = $jsonldDomain->getJsonldTypes();

		if ( ! empty( $post_types ) ) {

			$post_types = array_map( function( $post_type ) use ( $jsonldDomain ) {
				if ( is_numeric( $post_type ) ) {
					return $jsonldDomain->getReusablePostType( $post_type );
				}

				return $post_type;
			}, $post_types );

			//If a custom publisher is set
			if ( in_array( 'Publisher', $post_types ) || in_array( 'publisher', $post_types ) ) {
				add_filter( 'sqp_jsonld_publisher_url', function( $url ) {
					return $this->post->url;
				}, 11 );
			}
		}

		return $publisher->toArray();
	}

	public function getReview() {

		if ( ! empty( $this->_review ) ) {
			if ( ! count( array_filter( array_keys( $this->_review ), 'is_string' ) ) ) {
				foreach ( $this->_review as $index => $row ) {
					if ( ! isset( $row['reviewRating']['ratingValue'] ) || ! $row['reviewRating']['ratingValue'] ) {
						unset( $this->_review[ $index ] );
					}
				}
			} else {
				return SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Review', $this->_review );
			}
		}

		return $this->_review;
	}

	/**
	 * Get the aggregate ranking if reviews are found in schema
	 *
	 * @return mixed
	 */
	public function getAggregateRating() {

		if ( ! empty( $this->_review ) ) {

			$ratings = array();
			foreach ( $this->_review as $row ) {
				if ( isset( $row['reviewRating']['ratingValue'] ) && $row['reviewRating']['ratingValue'] > 0 ) {
					$ratings[] = $row['reviewRating']['ratingValue'];
				}
			}

			if ( ! empty( $ratings ) ) {
				$this->_aggregateRating = array(
					'ratingValue' => array_sum( $ratings ) / count( $ratings ),
					'reviewCount' => count( $ratings )
				);
			}
		}

		if ( isset( $this->_aggregateRating ) ) {

			/** @var SQP_Models_Domain_Jsonld_ARating $aRating */
			$aRating = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_ARating', $this->_aggregateRating );

			return $aRating->toArray();
		}

		return $this->_aggregateRating;
	}


	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array
	 */
	public function toArray() {
		return array();
	}

}
