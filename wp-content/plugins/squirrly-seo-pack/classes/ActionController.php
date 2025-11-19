<?php
defined('ABSPATH') || die('Cheatin\' uh?');

/**
 * Set the ajax action and call for WordPress
 */
class SQP_Classes_ActionController extends SQP_Classes_FrontController
{

    /**
     * All Actions
     * @var array with all form and ajax actions 
     */
    var $actions = array();

    public function getActionsList()
    {
        return array(
	        array(
		        'name' => 'SQP_Controllers_Redirects',
		        'actions' => array(
			        'action' => array(
				        'sqp_redirects_update',
				        'sqp_redirects_delete',
				        'sqp_redirects_disable',
				        'sqp_redirects_enable',
				        'sqp_log_delete',
				        'sq_ajax_rules_bulk_delete',
				        'sqp_redirects_settings_update',
				        'sq_redirects_import',
				        'sqp_redirects_export',
				        'sqp_settings_update',
				        'sq_redirects_backup',
				        'sq_redirects_restore',
			        ),
		        ),
		        'active' => '1',
	        ),

	        array(
		        'name' => 'SQP_Controllers_MediaLibrary',
		        'actions' => array(
			        'action' => array(
				        'sqp_medialibrary_search',
				        'sqp_medialibrary_create_image',
			        ),
		        ),
		        'active' => '1',
	        ),

	        array(
		        'name' => 'SQP_Controllers_Jsonld',
		        'actions' => array(
			        'action' => array(
				        'sqp_jsonld_get_jsonld_types',
				        'sqp_jsonld_edit',
				        'sqp_jsonld_add_key',
				        'sqp_jsonld_update',
				        'sqp_jsonld_preview',
				        'sqp_jsonld_delete',
				        'sq_jsonld_import',
				        'sq_jsonld_backup',
				        'sq_jsonld_restore',
				        'sqp_jsonld_get_reusable_jsonld_types',
				        'sqp_jsonld_reusable_edit',
				        'sqp_jsonld_reusable_update',
				        'sqp_jsonld_reusable_delete',
				        'sq_ajax_reusable_jsonld_bulk_delete',
			        ),
		        ),
		        'active' => '1',
	        ),
        );
    }

    /**
     * The hookAjax is loaded as custom hook in hookController class
     *
     * @return void
     */
    public function hookInit()
    {
        /* Only if ajax */
        if (SQP_Classes_Helpers_Tools::isAjax()) {
            $this->getActions();
        }
    }

    /**
     * The hookSubmit is loaded when action si posted
     *
     * @return void
     */
    public function hookMenu()
    {
        /* Only if post */
        if (!SQP_Classes_Helpers_Tools::isAjax()) {
            $this->getActions();
        }
    }

    /**
     * Get all actions from config.json in core directory and add them in the WP
     */
    public function getActions()
    {

        if (!is_admin()) {
            return;
        }

        $this->actions = array();
        $cur_action = SQP_Classes_Helpers_Tools::getValue('action');
        $http_referer = SQP_Classes_Helpers_Tools::getValue('_wp_http_referer');
        $sq_nonce = SQP_Classes_Helpers_Tools::getValue('sq_nonce');

        //Let only the logged users to access the actions
        if ($cur_action <> '' && $sq_nonce <> '') {

            //load the actions list for each class
            $actions = $this->getActionsList();

            if(!empty($actions)) {
                foreach ($actions as $block) {
                    if (isset($block['active']) && $block['active'] == 1) {
                        /* if there is a single action */
                        if (isset($block['actions']['action']))
                            /* if there are more actions for the current block */
                        if (!is_array($block['actions']['action'])) {
                            /* add the action in the actions array */
                            if ($block['actions']['action'] == $cur_action) {
                                $this->actions[] = array('class' => $block['name']);
                            }
                        } else {
                            /* if there are more actions for the current block */
                            foreach ($block['actions']['action'] as $action) {
                                /* add the actions in the actions array */
                                if ($action == $cur_action) {
                                    $this->actions[] = array('class' => $block['name']);
                                }
                            }
                        }
                    }
                }
            }

            //If there is an action found in the config.js file
            if (!empty($this->actions)) {

                /* add the actions in WP */
                foreach ($this->actions as $actions) {
                    if (SQP_Classes_Helpers_Tools::isAjax() && !$http_referer) {
                        check_ajax_referer(_SQP_NONCE_ID_, 'sq_nonce');
                        add_action('wp_ajax_' . $cur_action, array(SQP_Classes_ObjController::getClass($actions['class']), 'action'));
                    } else {
                        check_admin_referer($cur_action, 'sq_nonce');
                        SQP_Classes_ObjController::getClass($actions['class'])->action();
                    }
                }
            }
        }

    }

}
