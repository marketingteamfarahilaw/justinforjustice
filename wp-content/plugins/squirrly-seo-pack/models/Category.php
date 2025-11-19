<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Models_Category {

	/** @var string product base */
	private $product_base;
	/** @var string woocommerce categories */
	private $categories;

	public function __construct() {
		add_filter( 'sq_view', array( $this, 'loadSquirrlyCategoryView' ), 11, 2 );
	}

	/**
	 * Hook the Squirrly Snippet
	 *
	 * @param $file
	 * @param $block
	 *
	 * @return mixed|string
	 */
	public function loadSquirrlyCategoryView( $file, $block ) {

		// Image Search assets.
		if ( 'SeoSettings/Category' === $block ) {
			return _SQP_THEME_DIR_ . $block . '.php';
		}

		return $file;

	}

	/**
	 * Refresh the category rules in DB
	 */
	public function changeCategoryRefreshRules() {
		flush_rewrite_rules( false );
	}

	/**
	 * Refresh the rules on plugin deactivation
	 */
	public function removeCategoryRules() {
		remove_filter( 'category_rewrite_rules', array(
			$this,
			'changeCategoryRewriteRules'
		) ); // We don't want to insert our custom rules again

		$this->changeCategoryRefreshRules();
	}

	/**
	 * Removes category base from permalinks structure
	 *
	 * @return void
	 */
	public function changeCategoryPermalinks() {
		global $wp_rewrite;
		global $wp_version;

		if ( $wp_version >= 3.4 ) {
			$wp_rewrite->extra_permastructs['category']['struct'] = '%category%';
		} else {
			$wp_rewrite->extra_permastructs['category'][0] = '%category%';
		}
	}

	/**
	 * Remove the category slug from URL
	 *
	 * @param string $termlink Term link.
	 * @param object $term Current Term Object.
	 * @param string $taxonomy Current Taxonomy.
	 *
	 * @return array|string|string[]
	 */
	public function removeCategoryPath( $termlink, $term, $taxonomy ) {

		if ( 'category' !== $taxonomy ) {
			return $termlink;
		}

		$category_base = get_option( 'category_base' );
		if ( empty( $category_base ) ) {
			global $wp_rewrite;
			$category_base = trim( str_replace( '%category%', '', $wp_rewrite->get_category_permastruct() ), '/' );
		}

		// Remove initial slash, if there is one (we remove the trailing slash in the regex replacement and don't want to end up short a slash).
		if ( '/' === substr( $category_base, 0, 1 ) ) {
			$category_base = substr( $category_base, 1 );
		}

		$category_base .= '/';

		return preg_replace( '`' . preg_quote( $category_base, '`' ) . '`u', '', $termlink, 1 );
	}

	/**
	 * Integrate our personalized category rewrite rules
	 *
	 * @param array $category_rewrite Category rewrite rules.
	 *
	 * @return array
	 */
	public function changeCategoryRewriteRules( $category_rewrite ) {
		global $wp_rewrite;

		$category_rewrite = $this->getGategoryRules();

		$old_category_base                            = str_replace( '%category%', '(.+)', $wp_rewrite->get_category_permastruct() );
		$old_category_base                            = trim( $old_category_base, '/' );
		$category_rewrite[ $old_category_base . '$' ] = 'index.php?rank_math_category_redirect=$matches[1]';

		return $category_rewrite;

	}

	/**
	 * Get category rules.
	 *
	 * @return array
	 */
	private function getGategoryRules() {
		global $wp_rewrite;

		$category_rewrite = [];
		$categories       = $this->getCategories();
		$blog_prefix      = $this->getBlogPrefix();

		if ( empty( $categories ) ) {
			return $category_rewrite;
		}

		foreach ( $categories as $category ) {
			$category_nicename = $this->getCategoryParents( $category ) . $category->slug;
			$category_rewrite  = $this->addCategoryRewrites( $category_rewrite, $category_nicename, $blog_prefix, $wp_rewrite->pagination_base );

			// Add rules for upper case encoded
			$category_nicename_filtered = $this->convertToUpper( $category_nicename );

			if ( $category_nicename !== $category_nicename_filtered ) {
				$category_rewrite = $this->addCategoryRewrites( $category_rewrite, $category_nicename_filtered, $blog_prefix, $wp_rewrite->pagination_base );
			}
		}

		return $category_rewrite;
	}

	/**
	 * Get categories with WPML compatibility.
	 *
	 * @return array
	 */
	private function getCategories() {

		/* WPML is present: temporary disable terms_clauses filter to get all categories for rewrite */
		if ( class_exists( 'Sitepress' ) ) {
			global $sitepress;

			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
			$categories = get_categories( array( 'hide_empty' => false ) );
			add_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
		} else {
			$categories = get_categories( array( 'hide_empty' => false ) );
		}

		return $categories;
	}

	/**
	 * Get the website blog prefix
	 *
	 * @return string
	 */
	private function getBlogPrefix() {

		$permalink_structure = get_option( 'permalink_structure' );

		if ( is_multisite() && ! is_subdomain_install() && is_main_site() && 0 === strpos( $permalink_structure, '/blog/' ) ) {
			return 'blog/';
		}

		return '';
	}

	/**
	 * Retrieve category ancestors, including the separator.
	 *
	 * @param WP_Term $category
	 *
	 * @return string
	 */
	private function getCategoryParents( $category ) {

		if ( $category->parent === $category->cat_ID || absint( $category->parent ) < 1 ) {
			return '';
		}

		$parents = get_category_parents( $category->parent, false, '/', true );

		return is_wp_error( $parents ) ? '' : $parents;
	}

	/**
	 * Add category rewrite rules into WP array of rules.
	 *
	 * @param array $category_rewrite WP rewrites for categories
	 * @param string $category_nicename category base name
	 * @param string $blog_prefix WP blog prefix
	 * @param string $pagination_base WP_Query pagination base.
	 *
	 * @return array Rewrite Ruls
	 */
	private function addCategoryRewrites( $category_rewrite, $category_nicename, $blog_prefix, $pagination_base ) {

		$category_rewrite[ $blog_prefix . '(' . $category_nicename . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ]    = 'index.php?category_name=$matches[1]&feed=$matches[2]';
		$category_rewrite[ $blog_prefix . '(' . $category_nicename . ')/' . $pagination_base . '/?([0-9]{1,})/?$' ] = 'index.php?category_name=$matches[1]&paged=$matches[2]';
		$category_rewrite[ $blog_prefix . '(' . $category_nicename . ')/?$' ]                                       = 'index.php?category_name=$matches[1]';

		return $category_rewrite;
	}

	/**
	 * convert to uppercase and encode category name
	 *
	 * @param string $name Category name.
	 *
	 * @return string
	 */
	private function convertToUpper( $name ) {

		if ( strpos( $name, '%' ) === false ) {
			return $name;
		}

		$names = explode( '/', $name );
		$names = array_map( function( $encoded ) {

			if ( strpos( $encoded, '%' ) === false ) {
				return $encoded;
			}

			return strtoupper( $encoded );

		}, $names );

		return implode( '/', $names );
	}


	/**
	 * Change the category Query Vars
	 *
	 * @param $query_vars
	 *
	 * @return array
	 */
	public function changeCategoryQueryVars( $query_vars ) {
		$query_vars[] = 'category_redirect';

		return $query_vars;
	}

	/**
	 * Handles category redirects.
	 *
	 * @param array $query_vars current query vars.
	 *
	 * @return array $query_vars, or void if category_redirect is present.
	 */
	function removeCategory( $query_vars ) {

		if ( isset( $query_vars['category_redirect'] ) ) {
			$catlink = trailingslashit( get_option( 'home' ) ) . user_trailingslashit( $query_vars['category_redirect'], 'category' );
			status_header( 301 );
			header( "Location: $catlink" );
			exit();
		}

		return $query_vars;
	}


	///////////////////////////////////// WOOCOMMERCE SUPPORT

	/**
	 * Replace category permalink based on permalink preferences.
	 *
	 * @param string $link
	 * @param object $term
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	public function removeWoocomerceCategoryPath( $link, $term, $taxonomy ) {

		if ( $this->canChangeLink( 'product_cat', $taxonomy ) ) {
			return $link;
		}

		if ( ! function_exists( 'wc_get_permalink_structure' ) ) {
			return $link;
		}

		$permalink_structure  = wc_get_permalink_structure();
		$category_base        = trailingslashit( $permalink_structure['category_rewrite_slug'] );
		$is_language_switcher = ( class_exists( 'Sitepress' ) && strpos( $link, 'lang=' ) );

		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory_woocommerce' ) ) {
			$link          = str_replace( $category_base, '', $link );
			$category_base = '';
		}

		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_noslug_woocommerce' ) && ! $is_language_switcher ) {
			$link = home_url( user_trailingslashit( $category_base . $term->slug ) );
		}

		return $link;
	}

	/**
	 * Add Woocommerce category rewrite rules.
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	public function woocommerceRewriteRules( $rules ) {
		global $wp_rewrite, $sitepress;

		if ( ! function_exists( 'wc_get_permalink_structure' ) ) {
			return $rules;
		}

		if ( class_exists( 'Sitepress' ) ) {
			remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
		}

		$feed = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';

		$permalink_structure = wc_get_permalink_structure();
		$category_base       = SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory_woocommerce' ) ? '' : $permalink_structure['category_rewrite_slug'];
		$use_parent_slug     = ( stripos( '%product_cat%', $permalink_structure['product_rewrite_slug'] ) !== false );

		$product_rules  = [];
		$category_rules = [];
		foreach ( $this->getWooCommerceCategories() as $category ) {

			$cat_path = $this->getWooCommerceCategoriesFull( $category );
			$cat_slug = $category_base . ( SQP_Classes_Helpers_Tools::getOption( 'sq_noslug_woocommerce' ) ? $category['slug'] : $cat_path );

			$category_rules["{$cat_slug}/?\$"]                                             = 'index.php?product_cat=' . $category['slug'];
			$category_rules["{$cat_slug}/embed/?\$"]                                       = 'index.php?product_cat=' . $category['slug'] . '&embed=true';
			$category_rules["{$cat_slug}/{$wp_rewrite->feed_base}/{$feed}/?\$"]            = 'index.php?product_cat=' . $category['slug'] . '&feed=$matches[1]';
			$category_rules["{$cat_slug}/{$feed}/?\$"]                                     = 'index.php?product_cat=' . $category['slug'] . '&feed=$matches[1]';
			$category_rules["{$cat_slug}/{$wp_rewrite->pagination_base}/?([0-9]{1,})/?\$"] = 'index.php?product_cat=' . $category['slug'] . '&paged=$matches[1]';

			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory_woocommerce' ) && $use_parent_slug ) {
				$product_rules[ $cat_path . '/([^/]+)/?$' ]                                                           = 'index.php?product=$matches[1]';
				$product_rules[ $cat_path . '/([^/]+)/' . $wp_rewrite->comments_pagination_base . '-([0-9]{1,})/?$' ] = 'index.php?product=$matches[1]&cpage=$matches[2]';
			}
		}

		if ( class_exists( 'Sitepress' ) ) {
			add_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ), 10, 4 );
		}

		$rules = empty( $rules ) ? [] : $rules;

		return $category_rules + $product_rules + $rules;
	}

	/**
	 * Returns Woocommerce categories array.
	 *
	 * @return array
	 */
	private function getWooCommerceCategories() {

		if ( is_null( $this->categories ) ) {
			$categories = get_categories( [
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				] );

			$slugs = [];
			foreach ( $categories as $category ) {
				$slugs[ $category->term_id ] = [
					'parent' => $category->parent,
					'slug'   => $category->slug,
				];
			}

			$slugs = $this->getMultilingualCategories( $slugs );

			$this->categories = $slugs;
		}

		return $this->categories;
	}

	/**
	 * Add Polylang No Category support
	 *
	 * @param $slugs
	 *
	 * @return mixed
	 */
	public function getMultilingualCategories( $slugs ) {
		$languages = array();

		if ( function_exists( 'pll_languages_list' ) ) {
			$languages = pll_languages_list();
		}

		if ( ! empty( $slugs ) && ! empty( $languages ) ) {

			foreach ( $slugs as $term_id => $slug ) {
				foreach ( $languages as $language ) {
					if ( function_exists('pll_get_term') && $term_id = pll_get_term( $term_id, $language ) ) {
						if ( $category = get_term( $term_id ) ) {
							$slugs[ $category->term_id . $language ] = [
								'parent' => $category->parent,
								'slug'   => $language . '/' . $category->slug,
							];
						}

					}
				}
			}
		}

		return $slugs;
	}

	/**
	 * Recursively builds a category full path.
	 *
	 * @param object $category
	 *
	 * @return string
	 */
	private function getWooCommerceCategoriesFull( $category ) {

		$categories = $this->getWooCommerceCategories();
		$parent     = $category['parent'];

		if ( $parent > 0 && array_key_exists( $parent, $categories ) ) {
			return $this->getWooCommerceCategoriesFull( $categories[ $parent ] ) . '/' . $category['slug'];
		}

		return $category['slug'];

	}


	/**
	 * Modify product permalink based on the configured permalink settings.
	 *
	 * @param string $permalink
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function removeWoocomerceProductBase( $permalink, $post ) {

		if ( $this->canChangeLink( 'product', $post->post_type ) ) {
			return $permalink;
		}

		return str_replace( $this->get_product_base(), '/', $permalink );
	}

	/**
	 * Get product base name.
	 *
	 * @return string
	 */
	private function get_product_base() {

		if ( ! function_exists( 'wc_get_permalink_structure' ) ) {
			return $this->product_base;
		}

		if ( is_null( $this->product_base ) ) {

			$permalink_structure = wc_get_permalink_structure();
			$this->product_base  = $permalink_structure['product_rewrite_slug'];

			if ( strpos( $this->product_base, '%product_cat%' ) !== false ) {
				$this->product_base = str_replace( '%product_cat%', '', $this->product_base );
			}

			$this->product_base = '/' . trim( $this->product_base, '/' ) . '/';
		}

		return $this->product_base;
	}

	/**
	 * Determine whether the link is modifiable
	 *
	 * @param string $check Link
	 * @param string $against
	 *
	 * @return bool
	 */
	private function canChangeLink( $check, $against ) {
		return $check !== $against || ! get_option( 'permalink_structure' );
	}


	/**
	 * Replace request when a Woocommerce product is found.
	 *
	 * @param array $request Current product request.
	 *
	 * @return array
	 */
	public function productRequestCheck( $request ) {

		global $wp, $wpdb;
		$url = $wp->request;


		if ( empty( $url ) ) {
			return $request;
		}

		$replace = [];
		$url     = explode( '/', $url );
		$slug    = array_pop( $url );

		if ( 'feed' === $slug ) {
			$replace['feed'] = $slug;
			$slug            = array_pop( $url );
		}

		if ( 'amp' === $slug ) {
			$replace['amp'] = $slug;
			$slug           = array_pop( $url );
		}

		if ( 0 === strpos( $slug, 'comment-page-' ) ) {
			$replace['cpage'] = substr( $slug, strlen( 'comment-page-' ) );
			$slug             = array_pop( $url );
		}

		if ( 0 === strpos( $slug, 'schema-preview' ) ) {
			$replace['schema-preview'] = '';
			$slug                      = array_pop( $url );
		}

		$num = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) as count_id FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s", [
			$slug,
			'product'
		] ) ) ); // phpcs:ignore
		if ( $num > 0 ) {
			$replace['page']      = '';
			$replace['name']      = $slug;
			$replace['product']   = $slug;
			$replace['post_type'] = 'product';

			return $replace;
		}


		return $request;
	}

}
