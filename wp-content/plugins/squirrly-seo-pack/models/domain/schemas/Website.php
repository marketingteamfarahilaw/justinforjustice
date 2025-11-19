<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Website extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_organization;
	protected $_name;
	protected $_headline;
	protected $_description;
	protected $_mainEntityOfPage;
	protected $_thumbnailUrl;
	protected $_image;
	protected $_datePublished;
	protected $_dateModified;
	protected $_potentialAction;
	protected $_author;
	protected $_publisher;
	protected $_keywords;

	public function __construct( $properties = null ) {
		$jsonld = SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld' );

		$type = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Schemas_Organization' )->type;

		if ( isset( $jsonld[ $type ] ) ) {
			$this->_organization = $jsonld[ $type ];
		}

		parent::__construct( $properties );

	}

	public function getType() {
		return 'WebSite';
	}

	public function getHeadline() {

		if ( empty( $this->_headline ) ) {
			if ( isset( $this->_organization['name'] ) && $this->_organization['name'] ) {
				$this->_headline = $this->_organization['name'];
			} elseif ( isset( $this->post->sq->title ) ) {
				$this->_headline = $this->cleanText( $this->truncate( $this->post->sq->title, 0, $this->post->sq->jsonld_title_maxlength ) );
			}
		}

		return $this->_headline;
	}

	public function getMainEntityOfPage() {

		$page = array(
			'type' => 'WebPage',
			'url'  => $this->post->url
		);

		if ( ! empty( $this->_mainEntityOfPage ) ) {
			$page['type'] = $this->_mainEntityOfPage;
		}

		/** @var SQP_Models_Domain_Jsonld_Item $item */
		$item = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld_Item', $page );

		return $item->toArray();
	}

	public function getThumbnailUrl() {

		if ( empty( $this->_thumbnailUrl ) && ! empty( $this->post ) ) {
			if ( $this->post->sq->og_media <> '' ) {
				$this->_thumbnailUrl = $this->post->sq->og_media;
			}
		}

		return $this->_thumbnailUrl;
	}


	public function getDatePublished() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_datePublished );
	}

	public function getDateModified() {
		return apply_filters( 'sqp_jsonld_schema_datetime', $this->_dateModified );
	}

	public function getPotentialAction() {

		if ( empty( $this->_potentialAction ) ) {
			//Show search bar for products and shops
			//the str_replace is added for compatibility with WPML plugin
			if ( $this->post->post_type == 'product' || $this->post->post_type == 'shop' ) {
				$this->_potentialAction = array(
					'@type'       => 'SearchAction',
					'target'      => str_replace( 'search_term_string', '{search_term_string}', home_url( '?s=search_term_string&post_type=product' ) ),
					'query-input' => 'required name=search_term_string',
				);
			} else {
				$this->_potentialAction = array(
					'@type'       => 'SearchAction',
					'target'      => str_replace( 'search_term_string', '{search_term_string}', home_url( '?s=search_term_string' ) ),
					'query-input' => 'required name=search_term_string',
				);
			}
		}

		return $this->_potentialAction;
	}


	public function getKeywords() {

		if ( empty( $this->_keywords ) ) {
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
			'type'             => $this->type,
			'@id'              => $this->post->url . '#' . $this->type,
			'url'              => $this->post->url,
			'name'             => $this->headline,
			'headline'         => $this->headline,
			'description'      => $this->description,
			'mainEntityOfPage' => $this->mainEntityOfPage,
			'datePublished'    => $this->datePublished,
			'dateModified'     => $this->dateModified,
			'potentialAction'  => $this->potentialAction,
			'thumbnailUrl'     => $this->thumbnailUrl,
			'image'            => $this->image,
			'author'           => $this->author,
			'publisher'        => $this->publisher,
			'keywords'         => $this->keywords,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
