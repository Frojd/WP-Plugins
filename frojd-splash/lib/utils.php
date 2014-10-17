<?php

namespace Frojd\Plugins\Splash\Lib;

/**
 * Helper class for handling save permissions.
 */
class Utils {
    static function validateSave($post_id, $metabox_slug) {
        // Check if our nonce is set.
        if (! isset($_POST[$metabox_slug.'_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (! wp_verify_nonce($_POST[$metabox_slug.'_nonce'], $metabox_slug)) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
            if (! current_user_can( 'edit_page', $post_id)) {
                return;
            }

        } else if (! current_user_can('edit_post', $post_id)){
            return;
        }

        return true;
    }
}
