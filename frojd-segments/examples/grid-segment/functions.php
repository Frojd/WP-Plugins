<?php
/**
 * The segments metabox needs to be created from the add_meta_boxes action hook
 */
add_action('add_meta_boxes', 'addMetaBoxesHook');

/**
 * Uses functionality in segments plugin to create segment metaboxes
 */
function addMetaBoxesHook() {
    $postId = get_the_ID();
    $metaboxes = array();

    /* Any conditions can be used to only add this segment metabox to specific posts */
    if(has_post_format('gallery', $postId)) {
        $metaboxes['blurbs'] = array(
            'title'             => __('Select blurbs', get_translation_domain()), // defaults to "Select articles"
            'selection'         => array( // defaults to all posts, same parameters as in get_posts can be used here
                'post_type'         => array('blurbs'),
                'tax_query'         => array( // Any blurbs that aren't post format gallery can be selected from
                    array(
                        'taxonomy'      => 'post_format',
                        'field'         => 'slug',
                        'terms'         => array('post-format-gallery'),
                        'operator'      => 'NOT IN'
                    )
                )
            ),
            'show_on'           => array(
                'post_types'        => array('blurbs') // defaults to page
            ),
            'options'           => array(
                'grid'              => array( // Adds a grid option to each selected item where editor can select the width of item
                    'label'             => __('Grid', get_translation_domain()),
                    'type'              => 'select',
                    'options'           => array(
                        'full'              => '1/1', // The first object is the default one selected
                        'half'              => '1/2',
                        'quarter'           => '1/4'
                    )
                ),
                'featured'          => array(
                    'label'             => __('Featured', get_translation_domain()),
                    'type'              => 'checkbox',
                    'checked'           => true // Set the default value if it should be checked
                ),
                'alternative_title' => array() // No label will default to the key, and no type will default to text
            )
        );
    }

    // Call action with the complete metabox object to display the segment metabox on selected posts
    do_action('frojd_segments_add_metaboxes', $metaboxes);
}


/**
 * Example of how the post segments object can be added to all posts inside a loop where the global $post object is available
 */
add_action('the_post', 'thePostHook');

/**
 * Hook for the post which adds the segments as an attribute to the global post object
 */
function thePostHook($post) {
    // Adds a new value to the post object containing the post segments
    $segments = apply_filters('frojd_segments_get_segments', $post->ID);
    if(!empty($segments)) {
        $post->segments = (object) $segments;
    }
}


/**
 * Enqueueing admin scripts to add admin css for segments
 */
add_action('admin_enqueue_scripts', 'adminEnqueueScriptsHook');

/**
 * Hook for admin scripts to add the admin css, can be edited to only be visible on specific posts
 */
function adminEnqueueScriptsHook($page) {
    if($page == 'post.php' && get_post_type() == 'blurbs') {
        wp_enqueue_style('admin-style', get_template_directory_uri() . '/css/admin.css');
    }
}
?>