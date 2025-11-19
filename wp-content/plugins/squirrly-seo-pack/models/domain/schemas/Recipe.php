<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Recipe extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_image;
	protected $_datePublished;
	protected $_prepTime;
	protected $_cookTime;
	protected $_totalTime;
	protected $_recipeCategory;
	protected $_recipeCuisine;
	protected $_recipeYield;
	protected $_keywords;
	protected $_nutrition;
	protected $_recipeIngredient;
	protected $_recipeInstructions;
	protected $_instructionsSingleField;
	protected $_instructionsHowToStep;
	protected $_aggregateRating;
	protected $_review;
	protected $_author;
	protected $_video;
	protected $_publisher;

	public function getType() {
		return 'Recipe';
	}

	public function getRecipeInstructions() {

		if ( ! empty( $this->_recipeInstructions ) && $this->_recipeInstructions == 'SingleField' ) {
			return $this->_instructionsSingleField;
		}

		if ( ! empty( $this->_recipeInstructions ) && $this->_recipeInstructions == 'HowToStep' ) {
			return $this->_instructionsHowToStep;
		}

		return $this->_recipeInstructions;

	}

	public function getRecipeYield() {
		return apply_filters( 'sqp_jsonld_schema_array', $this->_recipeYield );
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {

		if ( empty($this->_video['name']) && empty($this->_video['contentUrl']) ) {
			$this->_video = array();
		}

		$array = array(
			'type'               => $this->type,
			'@id'                => $this->post->url . '#' . $this->type,
			'url'                => $this->post->url,
			'name'               => $this->name,
			'image'              => $this->image,
			'author'             => $this->author,
			'publisher'          => $this->publisher,
			'description'        => $this->description,
			'prepTime'           => $this->prepTime,
			'cookTime'           => $this->cookTime,
			'totalTime'          => $this->totalTime,
			'recipeCategory'     => $this->recipeCategory,
			'recipeCuisine'      => $this->recipeCuisine,
			'recipeYield'        => $this->recipeYield,
			'nutrition'          => $this->nutrition,
			'recipeIngredient'   => $this->recipeIngredient,
			'recipeInstructions' => $this->recipeInstructions,
			'video'              => $this->video,
			'review'             => $this->review,
			'aggregateRating'    => $this->aggregateRating,
			'keywords'           => $this->keywords,
		);

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
