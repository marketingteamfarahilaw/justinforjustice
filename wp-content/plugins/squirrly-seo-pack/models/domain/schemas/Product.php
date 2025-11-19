<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Domain_Schemas_Product extends SQP_Models_Domain_Schema {

	protected $_url;
	protected $_name;
	protected $_description;
	protected $_sku;
	protected $_brand;
	protected $_gtin8;
	protected $_mpn;
	protected $_isbn;
	protected $_offers;
	protected $_review;
	protected $_aggregateRating;
	protected $_image;
	protected $_publisher;
	protected $_shipping;
	protected $_return;

	//-- WooCommerce Product
	protected $_product;

	public function getType() {
		return 'Product';
	}

	public function getProduct( $properties = null ) {
		if ( empty( $this->_product ) ) {

			if ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'woocommerce/woocommerce.php' ) ) {
				// Generate structured data for Woocommerce 3+.
				if ( $this->post->post_type == 'product' ) {

					if ( function_exists( 'wc_get_product' ) ) {
						//create a new Woocommerce product based on post ID product
						if ( $woo_product = wc_get_product( $this->post->ID ) ) {
							if ( method_exists( $woo_product, 'get_id' ) && method_exists( $woo_product, 'get_price' ) ) {
								$this->_product = $woo_product;
							}
						}
					}
				}
			}

		}

		return $this->_product;

	}

	public function getName() {

		if ( $this->product && empty( $this->_name ) ) {
			if ( method_exists( $this->product, 'get_name' ) ) {
				$this->_name = $this->cleanText( $this->product->get_name() );
			}
		}

		return $this->_name;
	}

	public function getDescription() {

		if ( $this->product && empty( $this->_description ) ) {
			if ( method_exists( $this->product, 'get_short_description' ) ) {
				$this->_description = $this->cleanText( wp_strip_all_tags( $this->product->get_short_description() ? $this->product->get_short_description() : $this->product->get_description() ) );
			}
		}

		return $this->_description;
	}

	public function getSku() {

		if ( $this->product && empty( $this->_sku ) ) {

			if ( ! method_exists( $this->product, 'get_sku' ) || ! method_exists( $this->product, 'get_category_ids' ) ) {
				return $this->_sku;
			}

			$this->_sku = ( $this->product->get_sku() <> '' ? $this->product->get_sku() : '' );

			//Set default values if WooCommerce default is active
			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_product_defaults' ) ) {
				//Get all categories
				$categories = $this->product->get_category_ids();
				if ( ! empty( $categories ) ) {
					foreach ( $categories as $category ) {
						$category = get_term( $category, 'product_cat' );
						if ( isset( $category->name ) && $category->name <> '' ) {
							$this->_brand = array(
								'@type' => 'Brand',
								'name'  => $category->name,
							);
						}
					}
				}

				if ( $this->_sku == '' ) {
					$this->_sku = '-';
				}
				if ( $this->_mpn == '' ) {
					$this->_mpn = '-';
				}
			}
		}

		return $this->_sku;
	}

	public function getOffers() {

		if ( $offers = $this->getWooOffers() ) {
			if ( ! empty( $offers ) ) {
				/** @var SQP_Models_Jsonld_Sanitize $sanitize */
				$sanitize = SQP_Classes_ObjController::getClass( 'SQP_Models_Jsonld_Sanitize' );
				$sanitize->replaceArrayValuesRecursive( $this->_offers, $offers, true );
			}
		}

		if ( isset( $this->_offers ) && ! empty( $this->_offers ) ) {
			if ( $this->shipping == 'no' && isset( $this->_offers['shippingDetails'] ) ) {
				unset( $this->_offers['shippingDetails'] );
			}
			if ( $this->return == 'no' && isset( $this->_offers['hasMerchantReturnPolicy'] ) ) {
				unset( $this->_offers['hasMerchantReturnPolicy'] );
			}
			if ( isset( $this->_offers['priceSpecification']["valueAddedTaxIncluded"] ) ) {
				$this->_offers['priceSpecification']["valueAddedTaxIncluded"] = ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ? 'true' : 'false' );
			}
		}

		return $this->_offers;

	}

	public function getAggregateRating() {

		if ( ! empty( $this->_review ) ) {
			return parent::getAggregateRating();
		}

		if ( $this->product ) {

			//If rating and reviews
			if ( method_exists( $this->product, 'get_rating_count' ) && $this->product->get_rating_count() ) {

				//Only if it's set in Squirrly to remove duplicates
				//otherwise let Woocommerce show the reviews
				if ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_clearcode' ) ) {
					$this->_aggregateRating = array(
						'@type'       => 'AggregateRating',
						'ratingValue' => $this->product->get_average_rating(),
						'ratingCount' => $this->product->get_rating_count(),
						'reviewCount' => $this->product->get_review_count(),
					);
				}

			} else { //add default data?

				//Add data if no reviews for Google validation
				$this->_review['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => 5,
					'ratingCount' => 1,
					'reviewCount' => 1,
				);

			}

		}

		return $this->_aggregateRating;
	}

	public function getReview() {

		if ( ! empty( $this->_review ) ) {
			$this->_review = parent::getReview();
		}


		if ( empty( $this->_review ) && $this->product ) {

			if ( method_exists( $this->product, 'get_rating_count' ) ) {
				if ( $this->product->get_rating_count() ) {
					$this->_review = $this->getProductReview();
				} elseif ( SQP_Classes_Helpers_Tools::getOption( 'sq_jsonld_product_defaults' ) ) {
					//Add data if no reviews for Google validation
					$this->_aggregateRating = array(
						'@type'       => 'AggregateRating',
						'ratingValue' => 5,
						'ratingCount' => 1,
						'reviewCount' => 1,
					);

					$author        = $this->getAuthor();
					$this->_review = array(
						'@type'         => 'Review',
						'reviewRating'  => array(
							'@type'       => 'Rating',
							'ratingValue' => 5,
						),
						'author'        => array(
							'@type' => 'Person',
							'name'  => ( isset( $author['name'] ) ? $author['name'] : 'John Doe' ),
						),
						'reviewBody'    => ( $this->description ?: '-' ),
						'datePublished' => ( method_exists( $this->product, 'get_date_created' ) && $this->product->get_date_created() && method_exists( $this->product->get_date_created(), 'getTimestamp' ) ) ? gmdate( 'Y-m-d', $this->product->get_date_created()->getTimestamp() ) : '',
					);
				}
			}

		}

		return $this->_review;
	}

	/**
	 * Generates Review structured data.
	 *
	 * @return array | false
	 */
	private function getProductReview() {
		global $comment;
		$markup = array();


		if ( function_exists( 'wc_review_ratings_enabled' ) && wc_review_ratings_enabled() && function_exists( 'get_comments' ) && function_exists( 'get_comment_meta' ) ) {

			$comments = get_comments( array(
					'number'      => 10,
					'post_id'     => $this->product->get_id(),
					'status'      => 'approve',
					'post_status' => 'publish',
					'post_type'   => 'product',
					'parent'      => 0,
					'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => 'rating',
							'type'    => 'NUMERIC',
							'compare' => '>',
							'value'   => 0,
						),
					),
				) );

			if ( $comments ) {
				foreach ( $comments as $comment ) {
					$markup[] = array(
						'@type'         => 'Review',
						'reviewRating'  => array(
							'@type'       => 'Rating',
							'bestRating'  => '5',
							'ratingValue' => get_comment_meta( $comment->comment_ID, 'rating', true ),
							'worstRating' => '1',
						),
						'author'        => array(
							'@type' => 'Person',
							'name'  => get_comment_author( $comment ),
						),
						'reviewBody'    => get_comment_text( $comment ),
						'datePublished' => get_comment_date( 'c', $comment ),
					);

				}
			}
		}

		return $markup;

	}

	/**
	 * Get the offer from Woocommerce
	 *
	 * @return array|false
	 */
	private function getWooOffers() {

		if ( $this->product ) {

			if ( ! function_exists( 'wc_get_page_id' ) || ! function_exists( 'get_woocommerce_currency' ) || ! function_exists( 'wc_format_decimal' ) || ! function_exists( 'wc_get_price_decimals' ) || ! function_exists( 'wc_prices_include_tax' ) ) {
				return false;
			}

			//Get the product price
			$price     = $this->product->get_price();
			$currency  = get_woocommerce_currency();
			$shop_name = get_bloginfo( 'name' );
			$shop_url  = get_permalink( wc_get_page_id( 'shop' ) );

			//By default, set the price available for 1 year
			$price_valid_until = gmdate( 'Y-m-d', strtotime( '+12 Month' ) );
			if ( method_exists( $this->product, 'get_date_on_sale_to' ) && method_exists( $this->product, 'get_date_on_sale_from' ) && is_object( $this->product->get_date_on_sale_to() ) && is_object( $this->product->get_date_on_sale_from() ) ) {
				if ( $this->product->get_date_on_sale_from() && method_exists( $this->product->get_date_on_sale_from(), 'getTimestamp' ) ) {
					if ( $this->product->get_date_on_sale_from() && gmdate( 'Y-m-d', $this->product->get_date_on_sale_from()->getTimestamp() ) <= gmdate( 'Y-m-d' ) ) {
						if ( method_exists( $this->product->get_date_on_sale_to(), 'getTimestamp' ) ) {
							//Set the price available until the offer ends
							$price_valid_until = gmdate( 'Y-m-d', $this->product->get_date_on_sale_to()->getTimestamp() );
						}
					}
				}
			}

			//Get the price with VAT if exists
			if ( function_exists( 'wc_get_price_including_tax' ) && function_exists( 'wc_tax_enabled' ) ) {
				if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
					$price = wc_get_price_including_tax( $this->product, array( 'price' => $price ) );
				}
			}

			$markup_offer = array(
				'@type'           => 'Offer',
				'price'           => wc_format_decimal( $price, wc_get_price_decimals() ),
				'priceValidUntil' => $price_valid_until,
				'url'             => get_permalink( $this->product->get_id() ),
				'priceCurrency'   => $currency,
				'availability'    => 'https://schema.org/' . $stock = ( $this->product->is_in_stock() ? 'InStock' : 'OutOfStock' ),
			);

			//Get the variation prices
			if ( $this->product->is_type( 'variable' ) && method_exists( $this->product, 'get_variation_prices' ) && method_exists( $this->product, 'get_variation_regular_price' ) && method_exists( $this->product, 'get_variation_sale_price' ) ) {

				$prices = $this->product->get_variation_prices();

				if ( isset( $prices['price'] ) ) {
					//Get the product price
					$price    = $this->product->get_price();
					$minprice = $this->product->get_variation_price();
					$maxprice = $this->product->get_variation_price( 'max' );

					//Get the price with VAT if exists
					if ( function_exists( 'wc_get_price_including_tax' ) && function_exists( 'wc_tax_enabled' ) ) {
						if ( wc_tax_enabled() && ! wc_prices_include_tax() && 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) {
							$price    = wc_get_price_including_tax( $this->product, array( 'price' => $price ) );
							$minprice = wc_get_price_including_tax( $this->product, array( 'price' => $minprice ) );
							$maxprice = wc_get_price_including_tax( $this->product, array( 'price' => $maxprice ) );
						}
					}

					$markup_offer['priceSpecification'] = array(
						'@type'                 => 'UnitPriceSpecification',
						'price'                 => wc_format_decimal( $price, wc_get_price_decimals() ),
						'minPrice'              => wc_format_decimal( $minprice, wc_get_price_decimals() ),
						'maxPrice'              => wc_format_decimal( $maxprice, wc_get_price_decimals() ),
						'priceCurrency'         => $currency,
						'valueAddedTaxIncluded' => ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ? 'true' : 'false' ),
					);

					unset( $markup_offer['price'] );
					unset( $markup_offer['priceCurrency'] );
				}

			} elseif ( function_exists( 'wc_prices_include_tax' ) ) {
				$markup_offer['priceSpecification'] = array(
					'@type'                 => 'UnitPriceSpecification',
					'price'                 => wc_format_decimal( $this->product->get_price(), wc_get_price_decimals() ),
					'priceCurrency'         => $currency,
					'valueAddedTaxIncluded' => ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ? 'true' : 'false' ),
				);

				unset( $markup_offer['price'] );
				unset( $markup_offer['priceCurrency'] );
			}

			return $markup_offer;
		}

		return false;
	}

	/**
	 * Get the values as array and exclude the empty data
	 *
	 * @return array|mixed|null
	 */
	public function toArray() {
		$array = array();

		if ( empty( $array ) ) {
			$array = array(
				'type'            => $this->type,
				'@id'             => $this->post->url . '#' . $this->type,
				'url'             => $this->post->url,
				'name'            => $this->name,
				'description'     => $this->description,
				'sku'             => $this->sku,
				'gtin8'           => $this->gtin8,
				'mpn'             => $this->mpn,
				'isbn'            => $this->isbn,
				'brand'           => $this->brand,
				'offers'          => $this->offers,
				'review'          => $this->review,
				'aggregateRating' => $this->aggregateRating,
				'image'           => $this->image,
				'publisher'       => $this->publisher,

			);
		}

		return apply_filters( 'sqp_jsonld_schema_' . $this->type . '_array', array_filter( $array ), $this );

	}


}
