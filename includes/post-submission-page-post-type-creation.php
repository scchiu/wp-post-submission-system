<?php

/*
 * Register SPaper
 *
 */
// Initialization function
add_action('init', 'sp_cpt_spaper_init');
function sp_cpt_spaper_init() {
  // Create new News custom post type
    $spaper_labels = array(
    'name'                 => _x('SPapers', 'post type general name', 'post-submission-system'),
    'singular_name'        => _x('SPaper', 'post type singular name', 'post-submission-system'),
    'menu_name'            => _x('SPapers', 'admin menu', 'post-submission-system'), 
    'name_admin_bar'       => _x('SPaper', 'add admin on admin bar', 'post-submission-system'), 
    'add_new'              => _x('Add New', 'spaper', 'post-submission-system'),
    'add_new_item'         => __('Add New SPaper'),
    'new_item'             => __('New SPaper Item'),
    'edit_item'            => __('Edit SPaper Item'),
    'view_item'            => __('View SPaper Item'),
    'all_items'            => __('All SPapers','post-submission-system'), 
    'search_items'         => __('Search SPaper Items'),
    'parent_item_colon'    => '',
    'not_found'            => __('No SPapers found'),
    'not_found_in_trash'   => __('No SPapers found in Trash'), 
    '_builtin'             => false
  );
  $spaper_args = array(
    'labels'              => $spaper_labels,
    'public'              => true,
    'publicly_queryable'  => true,
    'exclude_from_search' => false,
    'show_ui'             => true,
    'show_in_menu'        => true, 
    'query_var'           => true,
    'rewrite'             => array( 
                                'slug' => 'spaper',
                                'with_front' => false
                             ),
    'capability_type'     => 'post',
    'has_archive'         => true,
    'hierarchical'        => false,
    'menu_position'       => 8,
    'menu_icon'           => 'dashicons-feedback',
    'supports'            => array('title','editor')
    //'supports'            => array('title','editor','thumbnail','excerpt','comments')
    //'taxonomies'          => array('post_tag')
  );
  register_post_type('spaper', $spaper_args);
}
/* Register Taxonomy */
add_action( 'init', 'spaper_taxonomies');
function spaper_taxonomies() {
    $labels = array(
        'name'              => _x( 'Category', 'taxonomy general name' ),
        'singular_name'     => _x( 'Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Category' ),
        'all_items'         => __( 'All Category' ),
        'parent_item'       => __( 'Parent Category' ),
        'parent_item_colon' => __( 'Parent Category:' ),
        'edit_item'         => __( 'Edit Category' ),
        'update_item'       => __( 'Update Category' ),
        'add_new_item'      => __( 'Add New Category' ),
        'new_item_name'     => __( 'New Category Name' ),
        'menu_name'         => __( 'Category' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'spaper-category' ),
    );

    register_taxonomy( 'spaper-category', array( 'spaper' ), $args );
}

register_activation_hook( __FILE__, 'spaper_rewrite_flush' );
function spaper_rewrite_flush() {  
    sp_cpt_spaper_init();  
    flush_rewrite_rules();
}


/***  Add custom column to post  ***/

//add_theme_support('post-thumbnails');
//add_image_size('featured_preview', 55, 55, true);
//remove_theme_support('Post Options');

// GET FEATURED IMAGE
function ST4_get_featured_image($post_ID) {
    $post_thumbnail_id = get_post_thumbnail_id($post_ID);
    if ($post_thumbnail_id) {
        $post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');
        return $post_thumbnail_img[0];
    }
}
function ST4_get_post_content($post_ID){
    $current_post = get_post($post_ID);
    return $current_post->post_content;
}

// ADD NEW COLUMN
function ST4_columns_head($defaults) {
    //$defaults['featured_image'] = 'Featured Image';
    //$defaults['content'] = __('Content');
    
    //insert custom column 'content' after 'title'
    $res = array_slice($defaults, 0, 2) + array('content' => __('Content')) 
            + array_slice($defaults, 2, count($defaults)-1, true);
    //print_r($res);
    return $res;
}
 
// SHOW THE FEATURED IMAGE
function ST4_columns_content($column_name, $post_ID) {
    if ($column_name == 'featured_image') {
        $post_featured_image = ST4_get_featured_image($post_ID);        
        if ($post_featured_image) {
            echo '<img src="' . $post_featured_image . '" />';
        }
        else{
            // NO FEATURED IMAGE, SHOW THE DEFAULT ONE
            echo '<img src="' . get_bloginfo( 'template_url' ) . '/images/default.jpg" />';            
        }
    }
    if($column_name == 'content'){
        echo ST4_get_post_content($post_ID);
    }
}


add_filter('manage_spaper_posts_columns', 'ST4_columns_head');
add_action('manage_spaper_posts_custom_column', 'ST4_columns_content', 10, 2);

// REMOVE DEFAULT CATEGORY COLUMN
add_filter('manage_spaper_posts_columns', 'ST4_columns_remove_category');
function ST4_columns_remove_category($defaults) {
    // to get defaults column names:
    //print_r($defaults);
    unset($defaults['language']);
    return $defaults;
}

/* hook: apply column modification to which post type */
/*
// ALL POST TYPES: posts AND custom post types
add_filter('manage_posts_columns', 'ST4_columns_head');
add_action('manage_posts_custom_column', 'ST4_columns_content', 10, 2);

// ONLY WORDPRESS DEFAULT POSTS
add_filter('manage_post_posts_columns', 'ST4_columns_head', 10);
add_action('manage_post_posts_custom_column', 'ST4_columns_content', 10, 2);

// ONLY WORDPRESS DEFAULT PAGES
add_filter('manage_page_posts_columns', 'ST4_columns_head', 10);
add_action('manage_page_posts_custom_column', 'ST4_columns_content', 10, 2);

// ONLY MOVIE CUSTOM TYPE POSTS
add_filter('manage_movie_posts_columns', 'ST4_columns_head_only_movies', 10);
add_action('manage_movie_posts_custom_column', 'ST4_columns_content_only_movies', 10, 2);
*/ 


/***  End of add custom column ***/

/*
add_action( 'wp_enqueue_scripts','style_css_script' );
function style_css_script() {
    wp_enqueue_style( 'cssnews',  plugin_dir_url( __FILE__ ). 'css/stylenews.css' );
    wp_enqueue_script( 'vticker', plugin_dir_url( __FILE__ ) . 'js/jcarousellite.js', array( 'jquery' ));
}
 * 
 */