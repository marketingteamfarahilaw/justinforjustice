<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Podcastepisode extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_datePublished;
	protected $_timeRequired;
	protected $_thumbnailUrl;
	protected $_isFamilyFriendly;
	protected $_associatedMedia;
	protected $_partOfSeason;
	protected $_episodeNumber;
	protected $_author;

	public function getType() {
		return 'PodcastEpisode';
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {
		$array = array();

		if ( $this->url <> '' ) {
			$array = array(
				'type'             => $this->type,
				'@id'              => $this->post->url . '#' . $this->type,
				'url'              => $this->url,
				'name'             => $this->name,
				'description'      => $this->description,
				'datePublished'    => $this->datePublished,
				'timeRequired'     => $this->timeRequired,
				'thumbnailUrl'     => $this->thumbnailUrl,
				'isFamilyFriendly' => $this->isFamilyFriendly,
				'associatedMedia'  => $this->associatedMedia,
				'partOfSeason'     => $this->partOfSeason,
				'episodeNumber'    => $this->episodeNumber,
				'author'           => $this->author,
			);
		}

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
