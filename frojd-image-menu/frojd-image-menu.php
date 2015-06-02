<?php
/**
 *
 * @package   Frojd Image Menu
 * @author    Fröjd
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Frojd - Image Menu
 * Plugin URI:  http://frojd.se
 * Description: Adds image support for menu items
 * Version:     1.0
 * Author:      Fröjd Interactive AB
 * Author URI:  http://frojd.se
 * License:     Fröjd Interactive AB (All Rights Reserved).
 */


class FrojdImageMenu {

    const VERSION = '1.0';
    protected $plugin_slug = 'frojd_image_menu';
    protected static $instance = null;

    protected $plugin_base;
    protected $plugin_rel_base;

    private function __construct() {
        $this->plugin_base = rtrim(dirname(__FILE__), '/');
        $this->plugin_rel_base = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_activation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        add_action('init', array($this, 'menuImageInit'));
        add_filter('manage_nav-menus_columns', array($this, 'menuImageNavMenuManageColumns'), 11);
        add_action('save_post', array($this, 'menuImageSavePostAction'), 10, 2);
        add_filter('wp_edit_nav_menu_walker', array($this, 'menuImageEditNavMenuWalkerFilter'));
        add_filter('upload_mimes', array($this, 'customUploadMimes'));
        add_shortcode('image_menu', array($this,'getImageMenu'));
    }

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }



    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function activationHook($network_wide) {
    }

    public function deactivationHook($network_wide) {
    }

    public static function uninstallHook($network_wide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/


    public function customUploadMimes($existing_mimes = array()) {

        // add the file extension to the array
        $existing_mimes['svg'] = 'image/svg';
        // call the modified list of extensions
        return $existing_mimes;
    }

    public function menuImageInit() {
        add_post_type_support('nav_menu_item', array('thumbnail'));
    }

    public function menuImageNavMenuManageColumns($columns) {
        return $columns + array('image' => __('Image', 'menu-image'));
    }

    public function menuImageSavePostAction($post_id, $post) {
        if (!empty($_FILES["menu-item-image_$post_id"])) {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');

            $attachment_id = media_handle_upload("menu-item-image_$post_id", $post_id);
            if ($attachment_id && is_int($attachment_id)) {
                set_post_thumbnail($post, $attachment_id);
            }
        }
        if (isset($_POST['menu_item_remove_image'][$post_id]) && !empty($_POST['menu_item_remove_image'][$post_id])) {
            $args = array(
                'post_type' => 'attachment',
                'post_status' => null,
                'post_parent' => $post_id,
            );
            $attachments = get_posts($args);
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    wp_delete_attachment($attachment->ID);
                }
            }
        }
    }

    public function getImageMenu($atts) {

        extract( shortcode_atts( array(
            'location' => null
        ), $atts));

        $locations = get_nav_menu_locations();

        if (($locations = get_nav_menu_locations()) && isset($locations[$location])) {
            $menu = wp_get_nav_menu_object($locations[$location]);
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            $output = '<ul class="menu-' . $location . '">';
            foreach ((array) $menu_items as $key => $menu_item) {
                $thumbnail_id = get_post_thumbnail_id($menu_item->ID);
                $thumbnail_object = get_post($thumbnail_id);
                if(isset($thumbnail_object->guid)) {
                    $output .= '
                        <li>
                            <a style="background-image:url('.$thumbnail_object->guid.')" target="'.$menu_item->target.'" href="'. $menu_item->url .'"><span>'. $menu_item->title .'</span></a>
                        </li>
                    ';
                }
            }
            $output .= '</ul>';
            return $output;
        }
    }


    public function menuImageEditNavMenuWalkerFilter() {
        return 'Menu_Image_Walker_Nav_Menu_Edit';
    }
}

FrojdImageMenu::get_instance();

require_once(ABSPATH . 'wp-admin/includes/nav-menu.php');
class Menu_Image_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

    function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0) {
        global $_wp_nav_menu_max_depth;
        $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

        ob_start();
        $item_id = esc_attr($item->ID);
        $removed_args = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        );

        $original_title = '';
        if ('taxonomy' == $item->type) {
            $original_title = get_term_field('name', $item->object_id, $item->object, 'raw');
            if (is_wp_error($original_title)) {
                $original_title = FALSE;
            }
        } elseif ('post_type' == $item->type) {
            $original_object = get_post($item->object_id);
            $original_title = $original_object->post_title;
        }

        $classes = array(
            'menu-item menu-item-depth-' . $depth,
            'menu-item-' . esc_attr($item->object),
            'menu-item-edit-' . ((isset($_GET['edit-menu-item']) && $item_id == $_GET['edit-menu-item']) ? 'active' : 'inactive'),
        );

        $title = $item->title;

        if (!empty($item->_invalid)) {
            $classes[] = 'menu-item-invalid';
            /* translators: %s: title of menu item which is invalid */
            $title = sprintf(__('%s (Invalid)'), $item->title);
        } elseif (isset($item->post_status) && 'draft' == $item->post_status) {
            $classes[] = 'pending';
            /* translators: %s: title of menu item in draft status */
            $title = sprintf(__('%s (Pending)'), $item->title);
        }

        $title = empty($item->label) ? $title : $item->label;

        $file = plugin_dir_path( __FILE__ ) . 'templates/admin-menu.php';
        include ($file);
        $output .= ob_get_clean();
    }
}
