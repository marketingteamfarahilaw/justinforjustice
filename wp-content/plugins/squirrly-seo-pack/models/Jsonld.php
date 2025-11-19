<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Jsonld {

	public function __construct() {

		//check if jsonld option is active
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_rich_snippets' ) && SQP_Classes_Helpers_Tools::getOption( 'sq_auto_jsonld' ) ) {
			//hook the Snippet in Squirrly SEO
			add_filter( 'sq_view', array(
				SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Snippet' ),
				'loadSnippetView'
			), 11, 2 );
			add_filter( 'sq_snippet_jsonld', array(
				SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Snippet' ),
				'loadSnippetJsonLd'
			), 11, 2 );

		}

	}

	/**
	 * Get the JsonLD Types
	 *
	 * @param $type
	 *
	 * @return array
	 */
	public function getJsonLdTypes( $type = false ) {

		$jsonld_types = array(
			'Article',
			'Book',
			'Course',
			'Event',
			'FAQ page',
			'HowTo',
			'JobPosting',
			'Movie',
			'Music',
			'Newsarticle',
			'Person',
			'PodcastEpisode',
			'Product',
			'Profile',
			'Publisher',
			'RealEstateListing',
			'Accommodation',
			'Recipe',
			'Review',
			'Restaurant',
			'Service',
			'SoftwareApplication',
			'Store',
			'Video',
			'VideoGame',
			'Carusel',
			'FactCheck',
			'Website',
			'WooCommerceProduct',
			'AggregateRating',
			'Breadcrumblist',
		);

		//merge all schema types
		return apply_filters( 'sqp_all_jsonld_types', $jsonld_types );

	}

	/**
	 * Get the JsonLD Types
	 *
	 * @return array
	 */
	public function getReusableJsonLdTypes() {
		$jsonld_types = array();

		/** @var SQP_Models_Domain_Jsonld $jsonldDomain */
		$jsonldDomain = SQP_Classes_ObjController::getDomain( 'SQP_Models_Domain_Jsonld' );
		//get the saved reusable schemas
		$reusable_schemas = $jsonldDomain->getReusables();

		if ( ! empty( $reusable_schemas ) ) {
			foreach ( $reusable_schemas as $reusable_schema ) {
				$jsonld_types[ $reusable_schema->id ] = $reusable_schema->name;
			}
		}

		return apply_filters( 'sqp_all_jsonld_reusable_types', $jsonld_types );
	}

	/**
	 * Get the JsonLD Types
	 *
	 * @param $type
	 *
	 * @return mixed|null
	 */
	public function getJsonLdClasses( $type = false ) {

		$jsonld_classes = array(
			'FAQPage' => array(
				'sq_question' => 'name',
				'sq_answer'   => 'text',
			),
		);

		$jsonld_classes = apply_filters( 'sqp_all_jsonld_classes', $jsonld_classes );

		if ( $type && isset( $jsonld_classes[ $type ] ) ) {
			return $jsonld_classes[ $type ];
		}

		return $jsonld_classes;
	}

	/**
	 * Check if there are schema classes in the content
	 *
	 * @param $post_id
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function checkSchemaClasses( $post_id, $post ) {

		$classes = array();
		foreach ( $this->getJsonLdClasses() as $subArray ) {
			foreach ( $subArray as $key => $value ) {
				$classes[] = $key;
			}
		}

		$classes = array_unique( $classes );

		if ( isset( $post->ID ) && isset( $post->post_type ) && $post->post_type <> '' ) {
			//If the post is a new or edited post
			if ( wp_is_post_autosave( $post->ID ) == '' && get_post_status( $post->ID ) <> 'auto-draft' && get_post_status( $post->ID ) <> 'inherit' ) {

				if ( isset( $post->post_content ) && $post->post_content <> '' ) {

					$content = '';

					//Check elementor content
					if ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'elementor/elementor.php' ) && ! SQP_Classes_Helpers_Tools::isPluginInstalled( 'elementpress/elementpress.php' ) ) {
						if ( class_exists( 'Elementor\Plugin' ) ) {
							try {
								$content = Elementor\Plugin::instance()->frontend->get_builder_content( $post->ID, true );
							} catch ( \Exception $e ) {
							}
						}
					}

					if ( $content == '' ) {
						$content = $post->post_content;
					}

					if ( ! empty( $classes ) && $content <> '' ) {
						foreach ( $classes as $class ) {

							if ( class_exists( 'DomDocument' ) && class_exists( 'DomXPath' ) ) {
								try {
									$dom = new DomDocument();
									@$dom->loadHTML( $content );
									$finder = new DomXPath( $dom );
									$nodes  = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]" );

									if ( ! empty( $nodes ) ) {
										$matches = array();

										foreach ( $nodes as $node ) {
											$node_dom = new DomDocument();
											$node_dom->appendChild( $node_dom->importNode( $node, true ) );
											$match = trim( $node_dom->saveHTML() );
											$matches[] = SQP_Classes_Helpers_Sanitize::sanitizeField( $match );
										}

										update_post_meta( $post_id, $class, $matches );
									}
								} catch ( Exception $e ) {
								}
							} else {
								preg_match_all( '/<([^>]*) [^>]*class=[\'"][^\'"]*' . $class . '[^\'"]*[\'"][^>]*>/i', $content, $matches );
								if ( ! empty( $matches[1] ) ) {

									$tags = array_unique( $matches[1] );
									foreach ( $tags as $tab ) {

										preg_match_all( '/<' . $tab . '\s[^>]*class=[\'"][^\'"]*' . $class . '[^\'"]*[\'"][^>]*>(.*)<\/' . $tab . '>/si', $content, $matches );
										if ( ! empty( $matches[1] ) ) {
											$matches[1] = array_map( function( $content ) {
												return SQP_Classes_Helpers_Sanitize::sanitizeField( $content );
											}, $matches[1] );
											update_post_meta( $post_id, $class, $matches[1] );
										}
									}

								}
							}

						}
					}


				}

			}
		}

	}


}
