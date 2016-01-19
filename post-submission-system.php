<?php
/*
  Plugin Name: post-submission-system
  Plugin URI: 
  Description: Provide shortcode [pss_page_shortcode] to create submission section and create admin management page
  Version: 1.0.0
  Author: Steve
  Author URI: 
  License: GPLv2+
  Text Domain: post-submission-system
*/

class post_submission_system{

  // Constructor
    function __construct() {                
        add_action('init', array($this, 'load_plugin_textdomain'));                
        require(dirname(__FILE__) . '/includes/post-submission-page-post-type-creation.php');        
        require(dirname(__FILE__) . '/includes/post-submission-page-shortcode.php');        
        //add_action( 'admin_menu', array( $this, 'pss_add_menu' ));
        register_activation_hook( __FILE__, array( $this, 'pss_install' ) );
        register_deactivation_hook( __FILE__, array( $this, 'pss_uninstall' ) );
    }

    /*
      * Actions perform at loading of admin menu
      */
    function pss_add_menu() {

        /*
        add_menu_page( 'Analytify simple', 'Analytify', 'manage_options', 'analytify-dashboard', array(
                          __CLASS__,
                         'wpa_page_file_path'
                        ), plugins_url('images/wp-analytics-logo.png', __FILE__),'2.2.9');

        add_submenu_page( 'analytify-dashboard', 'Analytify simple' . ' Dashboard', ' Dashboard', 'manage_options', 'analytify-dashboard', array(
                              __CLASS__,
                             'wpa_page_file_path'
                            ));

        add_submenu_page( 'analytify-dashboard', 'Analytify simple' . ' Settings', '<b style="//color:#f9845b">Settings</b>', 'manage_options', 'analytify-settings', array(
                              __CLASS__,
                             'wpa_page_file_path'
                            ));
         * 
         */           
        
        
        add_menu_page( 'Post Submission Page', '投稿文章管理', 'list_users', 'submission_post_list', 'my_render_list_page', 'dashicons-format-aside', 26);

        function my_render_list_page(){
            require_once(dirname(__FILE__) . '/includes/post-submission-list-table.php');
            $myListTable = new Post_Submission_List_Table();
            echo '<div class="wrap"><h2>投稿文章列表</h2>'; 
            $myListTable->prepare_items(); 
            $myListTable->display(); 
            echo '</div>'; 
        }
          
    }

    /*
     * Actions perform on loading of menu pages
     */
    
    /*
    function wpa_page_file_path() {
        $screen = get_current_screen();
        if ( strpos( $screen->base, 'analytify-settings' ) !== false ) {
            include( dirname(__FILE__) . '/includes/analytify-settings.php' );
        } 
        else {
            include( dirname(__FILE__) . '/includes/analytify-dashboard.php' );
        }
    }
     * 
     */

    /*
     * Actions perform on activation of plugin
     */
    function pss_install() {
    }

    /*
     * Actions perform on de-activation of plugin
     */
    function pss_uninstall() {
    }
    
    public function load_plugin_textdomain(){
        //load_theme_textdomain('themeton',false, basename(dirname(__FILE__)) . '/languages');
        //load_textdomain('themeton', MY_ADMIN_BAR__PLUGIN_DIR.'languages/zh_TW.mo');
        load_textdomain('default', WP_LANG_DIR.'/admin-'.get_locale().'.mo');
        //languages
        load_plugin_textdomain( 'post-submission-system', false, basename(dirname(__FILE__)) . '/languages/');        
        
    }
}

new post_submission_system();