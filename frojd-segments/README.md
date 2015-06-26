# Fröjd Segments

Plugin to handle segment metaboxes where the editor can drag'n'drop articles from a list of available articles, into a sorted list which then can be used in different ways in the theme. This functionality is mainly built from a "featured articles" point of view where the editor can e.g. select related articles for a specific article.

Using a hook included in the theme, the segment metabox can be added to specific articles in the admin edit view. So far there is implementation to add the metabox to specific post types only.

## Usage
Below are examples of how to create a segments metabox and how to get it inside a template. Other examples can be seen inside the examples directory.

### Add meta box to edit view
The action "frojd_segments_metabox" can be called anywhere in the theme, most likely in functions.php. With specific parameters sent to the action hook, a metabox will be visible in the admin edit view of that type of article. This action should be called inside a function triggered by the "add_meta_boxes" action. Each segments metabox will show a list of all the available posts selected from the "selection" parameter. When an item is dragged to the selected list the options defined in the "options" parameter will be available in a small popup which opens when clicking the settings icon.

#### Example
```php
add_action('add_meta_boxes', 'addMetaBoxesHook');
function addMetaBoxesHook() {
    $metaboxes = array();
    $metaboxes['segments_metabox'] = array(
        'title'             => __('Select my articles', get_translation_domain()), // defaults to "Select articles"
        'selection'         => array( // defaults to all posts, same parameters as in get_posts can be used here
            'post_type'         => array('page')
        ),
        'show_on'           => array(
            'post_types'        => array('page'), // defaults to page
            'valid_terms'       => array( // defaults to none, uses same parameters as has_term, term can be name/id/slug or array of them to check for, taxonomy is the name
                array('taxonomy' => 'term')
            ),
            'post_meta'         => array( // defaults to none, uses get_post_meta function to check, key is the field name and value is what to check for
                array('key' => 'value')
            )
        ),
        'options'           => array( // Options to be added to each item selected in the segment, can be used to e.g. implement grid layout or add an alternative title.
            'layout'            => array(
                'label'             => __('Grid layout', get_translation_domain()),
                'type'              => 'select', // Can be any text input field (text, number etc), select, checkbox or textarea
                'options'           => array( // Key and value of the options in select e.g. array('quarter' => '1/4')
                    'value' => 'name'
                )
            )
        )
    );
    do_action('frojd_segments_metabox', $metaboxes);
}
```

### Retreive the segments from posts
The meta boxes and the posts selected can be retrieved in different ways. The filter "frojd_segments_get_segment" can accept two parameters (post id, and the meta box id) and will return the selected segment. The filter "frojd_segments_get_segments" can accept one parameter (post id), and will return the whole segments object with all the meta boxes for that post and including the selected posts in that. Otherwise th easiest way to implement the segments is to hook into the "the_post" function and add an attribute for the segments to the post object, seen in the example below. When this is done the segments object is available inside each loop where the global $post object is available.

#### Example
```php
add_action('the_post', 'thePostHook');
function thePostHook($post) {
    // Adds a new value to the post object containing the post segments
    $segments = apply_filters('frojd_segments_get_segments', $post->ID);
    $post->segments = (object) $segments;
}
```