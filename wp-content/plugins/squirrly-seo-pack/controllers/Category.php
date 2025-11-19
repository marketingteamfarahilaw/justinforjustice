<?php
defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

class SQP_Controllers_Category extends SQP_Classes_FrontController {

	public function __construct() {
		parent::__construct();

		if ( get_transient( 'sqp_flush_rules' ) ) {
			delete_transient( 'sqp_flush_rules' );
			add_action( 'wp_loaded', array( $this->model, 'changeCategoryRefreshRules' ) );
		}

		/* hooks */
		if ( SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory' ) ) {
			/* actions */
			add_action( 'created_category', array( $this->model, 'changeCategoryRefreshRules' ) );
			add_action( 'delete_category', array( $this->model, 'changeCategoryRefreshRules' ) );
			add_action( 'edited_category', array( $this->model, 'changeCategoryRefreshRules' ) );

			/* filters */
			add_filter( 'category_rewrite_rules', array( $this->model, 'changeCategoryRewriteRules' ) );
			add_filter( 'query_vars', array( $this->model, 'changeCategoryQueryVars' ) );
			add_filter( 'request', array( $this->model, 'removeCategory' ) );
			add_filter( 'term_link', array( $this->model, 'removeCategoryPath' ), 10, 3 );
		}

		if ( SQP_Classes_Helpers_Tools::isPluginInstalled( 'woocommerce/woocommerce.php' ) ) {
			//Add support for WooCommerce
			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_nocategory_woocommerce' ) || SQP_Classes_Helpers_Tools::getOption( 'sq_noslug_woocommerce' ) ) {
				/* actions */
				add_action( 'created_product_cat', array( $this->model, 'changeCategoryRefreshRules' ) );
				add_action( 'delete_product_cat', array( $this->model, 'changeCategoryRefreshRules' ) );
				add_action( 'edited_product_cat', array( $this->model, 'changeCategoryRefreshRules' ) );

				add_filter( 'term_link', array( $this->model, 'removeWoocomerceCategoryPath' ), 0, 3 );
				add_filter( 'rewrite_rules_array', array( $this->model, 'woocommerceRewriteRules' ), 99 );
			}

			if ( SQP_Classes_Helpers_Tools::getOption( 'sq_noproduct_woocommerce' ) && ! SQP_Classes_Helpers_Tools::getValue( 'elementor-preview' ) ) {
				//Remove the Product Base for Woocommerce websites
				add_filter( 'post_type_link', array( $this->model, 'removeWoocomerceProductBase' ), 1, 2 );
				add_filter( 'request', array( $this->model, 'productRequestCheck' ), 11 );
			}
		}

	}


}
