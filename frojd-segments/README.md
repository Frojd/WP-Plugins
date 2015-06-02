# Fröjd Segments

Plugin to handle segment metaboxes where the editor can drag'n'drop articles from a list of available articles, into a sorted list which then can be used in different ways in the theme. This functionality is mainly built from a "featured articles" point of view where the editor can e.g. select related articles for a specific article.

Using a hook included in the theme, the segment metabox can be added to specific articles in the admin edit view. So far there is implementation to add the metabox to specific post types only.

## Usage

### Add meta box to edit view
The action "frojd_segments_metabox" can be called anywhere in the theme, most likely in functions.php. With specific parameters sent to the action hook, a metabox will be visible in the admin edit view of that type of article. This action should be called inside a function triggered by the "add_meta_boxes" action.

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
        )
    );
    do_action('frojd_segments_add_metaboxes', $metaboxes);
}
```

### Retreive the segments from posts
The meta boxes and the posts selected can be retrieved in different ways. The filter "frojd_segments_get_segment" can accept two parameters (post id, and the meta box id) and will return the selected segment. The filter "frojd_segments_get_segments" can accept one parameter (post id), and will return the whole segments object with all the meta boxes for that post and including the selected posts in that. Otherwise th easiest way to implement the segments is to hook into the "the_post" function and add an attribute for the segments to the post object, seen in the example below.

#### Example
```php
add_action('the_post', 'thePostHook');
function thePostHook($post) {
    // Adds a new value to the post object containing the post segments
    $segments = apply_filters('frojd_segments_get_segments', $post->ID);
    $post->segments = (object) $segments;
}
```
