<?php

namespace Frojd\Plugins\Splash\Lib;

/**
 * This class makes it easier to create metaboxes.
 */
class Metabox {
    public $id = '';
    public $title = '';
    public $template = 'metabox';
    public $screens = array('post');
    public $fields = array();
    public $context = 'advanced';
    public $priority = 'default';
    public $data = array();

    public function __construct($options) {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }

        add_action('add_meta_boxes', array(&$this, 'metabox_hook'));
        add_action('save_post', array(&$this, 'save_post_hook'));
    }


    /* Hooks
    =========================================================================== */

    public function metabox_hook() {
        $screens = $this->screens;

        foreach ($screens as $screen) {
            add_meta_box(
                $this->id . '_' . $screen,
                $this->title,
                array(&$this, 'metabox_callback'),
                $screen,
                $this->context,
                $this->priority
            );
        }
    }

    public function metabox_callback($post) {
        wp_nonce_field($this->id, $this->id .'_nonce');

        View::render($this->template, array_merge($this->data, array(
            'fields' => $this->get_fields($post->ID),
            'post' => $post,
        )));
    }

    public function save_post_hook($post_id) {
        if (!Utils::validateSave($post_id, $this->id)) {
            return;
        }

        // Save meta fields
        foreach ($this->get_fields($post_id) as $field) {
            if ($field['type'] === 'text' || $field['type'] === 'password' ||
                $field['type'] === 'select' || $field['type'] === 'hidden') {

                update_post_meta($post_id, $field['key'],
                    Request::post($field['name'], 0));
            }
        }

        //## Need ability to extend with custom behavior,
        //## eg function_exists($this->save_posts_hook_after()) -> call it
    }

    public function get_fields($post_id) {
        $fields = $this->fields;

        //## Cache this
        //## Make it public?

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'text' || $field['type'] === 'password' ||
                $field['type'] === 'select' || $field['type'] === 'hidden') {

                $fields[$key]['value'] = get_post_meta(
                    $post_id, $field['key'],
                    true
                );
            }
            //## Handle other types
        }

        return $fields;
    }

}
