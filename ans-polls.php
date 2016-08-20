<?php
/**
 * Anspress Polls
 *
 * Polls for AnsPress
 *
 * @package   Anspress_Polls
 * @author    Tonino Jankov <tyaakow@gmail.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Tonino Jankov
 *
 * @wordpress-plugin
 * Plugin Name:       Anspress Polls
 * Plugin URI:        http://wp3.in
 * Description:       Polls for AnsPress
 * Version:           0.0.1
 * Author:            Tonino Jankov
 * Author URI:        
 * Text Domain:       ans-polls
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: git@github.com:tjankov/ans-polls.git
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


class AnsPress_Ext_Anspress_Polls
{

    /**
     * Class instance
     * @var object
     * @since 1.0
     */
    private static $instance;


    /**
     * Get active object instance
     *
     * @since 1.0
     *
     * @access public
     * @static
     * @return object
     */
    public static function get_instance() {

        if ( ! self::$instance )
            self::$instance = new AnsPress_Ext_Anspress_Polls();

        return self::$instance;
    }
    /**
     * Initialize the class
     * @since 0.0.1
     */
    public function __construct()
    {
        if( ! class_exists( 'AnsPress' ) )
            return; // AnsPress not installed

        if (!defined('ANSPRESS_POLLS_DIR'))
            define('ANSPRESS_POLLS_DIR', plugin_dir_path( __FILE__ ));

        if (!defined('ANSPRESS_POLLS_URL'))
                define('ANSPRESS_POLLS_URL', plugin_dir_url( __FILE__ ));

        $this->includes();

        // internationalization
        add_action( 'init', array( $this, 'textdomain' ) );

        add_action('ap_admin_menu', array($this, 'admin_menu'));
        add_filter('ap_default_options', array($this, 'ap_default_options') );
        add_action( 'ap_enqueue', array( $this, 'ap_enqueue' ) );

        add_action('ap_ask_form_fields', array($this, 'ask_from_field'), 10, 2);
        add_action('ap_ask_fields_validation', array($this, 'ap_ask_fields_validation'));
    }

    public function includes(){
       // require_once( ANSPRESS_POLLS_DIR . 'file.php' );
    }

    /**
     * Load plugin text domain
     *
     * @since 0.0.1
     *
     * @access public
     * @return void
     */
    public static function textdomain() {

        // Set filter for plugin's languages directory
        $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

        // Load the translations
        load_plugin_textdomain( 'Anspress_Polls', false, $lang_dir );

    }


    /**
     * Apppend default options
     * @param   array $defaults
     * @return  array
     * @since   1.0
     */
    public function ap_default_options($defaults)
    {
        $defaults['enable_categories']  = true;

        return $defaults;
    }

    /**
     * Add Anspress Polls menu in wp-admin
     * @return void
     * @since 2.0
     */
    public function admin_menu(){
        //add_submenu_page('anspress', 'Anspress Polls', 'Anspress_Polls', 'manage_options', 'edit-tags.php?taxonomy=question_category');
    }

    /**
     * Register Anspress Polls option tab in AnsPress options
     * @param  array $navs Default navigation array
     * @return array
     * @since 0.0.1
     */
    public function option_navigation($navs){
        $navs['Anspress_Polls'] =  __('ANSPRESS_POLLS', 'Anspress_Polls');
        return $navs;
    }

    /**
     * Enqueue scripts
     * @since 0.0.1
     */
    public function ap_enqueue()
    {
        wp_enqueue_style( 'Anspress_Polls_css', ap_get_theme_url('css/Anspress_Polls.css', ANSPRESS_POLLS_URL));

    }

    /**
     * add Anspress Polls field in ask form
     * @param  array $validate
     * @return void
     * @since 0.0.1
     */
    public function ask_from_field($args, $editing){
        /*global $editing_post;

        if($editing){
            $category = get_the_terms( $editing_post->ID, 'question_category' );
            $catgeory = $category[0]->term_id;
        }

        $args['fields'][] = array(
            'name' => 'category',
            'label' => __('Category', 'ap'),
            'type'  => 'taxonomy_select',
            'value' => ( $editing ? $catgeory :  sanitize_text_field(@$_POST['category'] ))  ,
            'taxonomy' => 'question_category',
            'desc' => __('Select a topic that best fits your question', 'ap'),
            'order' => 6
        );*/

        return $args;
    }

    /**
     * add Anspress Polls in validation field
     * @param  array $fields
     * @return array
     * @since  0.0.1
     */
    public function ap_ask_fields_validation($args){
       /* $args['category'] = array(
            'sanitize' => array('only_int'),
            'validate' => array('required'),
        );*/

        return $args;
    }

}

/**
 * Get everything running
 *
 * @since 1.0
 *
 * @access private
 * @return void
 */

function anspress_ext_Anspress_Polls() {
    $anspress_ext_Anspress_Polls = new AnsPress_Ext_Anspress_Polls();
}
add_action( 'plugins_loaded', 'anspress_ext_Anspress_Polls' );

/**
 * Register activatin hook
 * @return void
 * @since  1.0
 */
function activate_Anspress_Polls(){
    // create and check for categories base page

    /*$page_to_create = array('question_categories' => __('Categories', 'categories_for_anspress'), 'question_category' => __('Category', 'categories_for_anspress'));

    foreach($page_to_create as $k => $page_title){
        // create page

        // check if page already exists
        $page_id = ap_opt("{$k}_page_id");

        $post = get_post($page_id);

        if(!$post){

            $args['post_type']          = "page";
            $args['post_content']       = "[anspress_{$k}]";
            $args['post_status']        = "publish";
            $args['post_title']         = $page_title;
            $args['comment_status']     = 'closed';
            $args['post_parent']        = ap_opt('questions_page_id');

            // now create post
            $new_page_id = wp_insert_post ($args);

            if($new_page_id){
                $page = get_post($new_page_id);
                ap_opt("{$k}_page_slug", $page->post_name);
                ap_opt("{$k}_page_id", $page->ID);
            }
        }
    }*/
}
register_activation_hook( __FILE__, 'activate_Anspress_Polls'  );
