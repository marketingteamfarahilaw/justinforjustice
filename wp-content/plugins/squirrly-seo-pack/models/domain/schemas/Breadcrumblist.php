<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Breadcrumblist extends SQP_Models_Domain_Schema {

	protected $_type;
	protected $_itemListElement;

	public function getType() {

		if ( empty( $this->_type ) ) {
			$this->_type = 'BreadcrumbList';
		}

		return $this->_type;
	}

	public function getItemListElement() {
		if ( empty( $this->_itemListElement ) || empty( array_filter( array_column( (array) $this->_itemListElement, 'position' ) ) ) ) {
			$this->_itemListElement = $this->getBreadcrumbsMarkup();
		}

		if ( ! empty( $this->_itemListElement ) ) {
			foreach ( $this->_itemListElement as &$item ) {
				if ( isset( $item['url'] ) ) {
					$item['item'] = $item['url'];
					unset( $item['url'] );
				}
			}
		}

		return apply_filters( 'sqp_breadcrumbs_items', $this->_itemListElement );
	}

	/**
	 * Generates BreadcrumbList structured data.
	 *
	 * @return array
	 */
	public function getBreadcrumbsMarkup() {

		$itemListElements = $root = $crumbs = $lists = array();

		//show the breadcrumbs
		if ( $this->post->post_type <> '' && $this->post->post_type <> 'home' ) {
			///////////////////////////// Home Page
			if(class_exists('SQ_Classes_ObjController')) {
				$post = SQ_Classes_ObjController::getClass( 'SQ_Models_Snippet' )->setHomePage();

				if ( $post->ID == 0 || $this->post->ID <> $post->ID ) {
					$root[] = array(
						( $post->sq->title <> '' ? $post->sq->title : $post->post_title ),
						$post->url,
					);
				}
			}

			if ( $this->post->post_type == 'category' && isset( $this->post->term_id ) && isset( $this->post->taxonomy ) ) {
				$parents = get_ancestors( $this->post->term_id, $this->post->taxonomy );
				if ( ! empty( $parents ) ) {
					$parents = array_reverse( $parents );

					foreach ( $parents as $parent ) {
						$parent = get_term( $parent );
						if ( ! is_wp_error( $parent ) ) {
							$crumbs[] = array(
								$parent->name,
								get_term_link( $parent->term_id, $this->post->taxonomy ),
							);
						}
					}

					$lists[] = $crumbs;
				}
			} elseif ( $this->post->post_type == 'product' ) {
				if ( class_exists( 'WC_Product' ) ) {
					$product = new WC_Product( $this->post->ID );

					//Get all categories
					if ( $product instanceof WC_Product ) {
						$taxonomy = 'product_cat';

						if ( (int) $this->post->sq->primary_category > 0 ) {
							//check if the primary category was selected by the client
							$category_ids = array( (int) $this->post->sq->primary_category );
						} else {
							$category_ids = $product->get_category_ids();
						}

						if ( ! empty( $category_ids ) ) {
							foreach ( $category_ids as $category ) {
								$parents = get_ancestors( $category, $taxonomy );

								if ( ! empty( $parents ) ) {

									foreach ( $parents as $parent ) {
										$parent = get_term( $parent );
										if ( ! is_wp_error( $parent ) ) {
											$crumbs[] = array(
												$parent->name,
												get_term_link( $parent->term_id, $taxonomy ),
											);
										}
									}

									$category = get_term( $category, $taxonomy );
									if ( isset( $category->name ) && $category->name <> '' ) {
										$crumbs[] = array(
											$category->name,
											get_term_link( $category->term_id, $taxonomy ),
										);
									}

									$lists[] = $crumbs;
								} else {

									$category = get_term( $category, $taxonomy );
									if ( isset( $category->name ) && $category->name <> '' ) {
										$crumbs[] = array(
											$category->name,
											get_term_link( $category->term_id, $taxonomy ),
										);
									}

									$lists[] = $crumbs;
								}
							}
						}
					}
				}
			} else {
				/////////////////////// Parent Categories
				if ( (int) $this->post->sq->primary_category > 0 ) {
					$categories = array( get_category( (int) $this->post->sq->primary_category ) );
				} else {
					$categories = get_the_category( $this->post->ID );
				}

				if ( ! empty( $categories ) ) {
					foreach ( $categories as $category ) {
						if ( isset( $category->term_id ) && isset( $category->taxonomy ) ) {
							$crumbs  = [];
							$parents = get_ancestors( $category->term_id, $category->taxonomy );

							if ( ! empty( $parents ) ) {
								$parents = array_reverse( $parents );

								foreach ( $parents as $parent ) {
									$parent = get_term( $parent );
									if ( isset( $parent->name ) && $parent->name <> '' ) {
										$crumbs[] = array(
											$parent->name,
											get_term_link( $parent->term_id, $category->taxonomy ),
										);
									}
								}

								if ( isset( $category->name ) && $category->name <> '' ) {
									$crumbs[] = array(
										$category->name,
										get_term_link( $category->term_id, $category->taxonomy ),
									);
								}

								$lists[] = $crumbs;
							} elseif ( isset( $category->name ) && $category->name <> '' ) {
								$crumbs[] = array(
									$category->name,
									get_term_link( $category->term_id, $category->taxonomy ),
								);

								$lists[] = $crumbs;
							}
						}
					}
				}
			}


			if ( ! empty( $crumbs ) ) {

				foreach ( $lists as $list ) {
					//merge and reset the keys
					$list = array_merge( $root, $list );
					$list = array_values( $list );

					////////////////////// Current post
					$list[] = array(
						( $this->post->sq->title <> '' ? $this->post->sq->title : $this->post->post_title ),
						$this->post->url,
					);

					$itemListElement = array();
					foreach ( $list as $key => $crumb ) {
						$itemListElements[] = array(
							'@type'    => 'ListItem',
							'position' => $key + 1,
							'item'     => array(
								'@id'  => $crumb[1],
								'name' => SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' )->cleanText( $crumb[0] )
							),
						);
					}

				}
			}
		}

		return $itemListElements;

	}

	public function toArray() {

		$array = array();

		if ( ! empty( $this->itemListElement ) ) {
			$array = array(
				'type'            => $this->type,
				'@id'             => $this->post->url . "#" . strtolower( $this->type ),
				'itemListElement' => $this->itemListElement,
			);
		}

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );
	}


}
