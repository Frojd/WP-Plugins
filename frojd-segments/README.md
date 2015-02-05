# Fröjd Segments

Plugin to handle segment metaboxes where the editor can drag'n'drop articles from a list of available articles, into a sorted list which then can be used in different ways in the theme. This functionality is mainly built from a "featured articles" point of view where the editor can e.g. select related articles for a specific article.

Using a hook included in the theme, the segment metabox can be added to specific articles in the admin edit view. So far there is implementation to add the metabox to specific post types only.

## Usage
    
The action "frojd_segments_metabox" can be called anywhere in the theme, most likely in functions.php. With specific parameters sent to the action hook, a metabox will be visible in the admin edit view of that type of article.

### Example
```php
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
do_action('frojd_segments_metabox', $metaboxes);
```