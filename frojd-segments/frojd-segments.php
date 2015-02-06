<?php
/**
 *
 * @package   Fröjd - Segments
 * @author    Fröjd - Sara Öjelind
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Fröjd - Segments
 * Plugin URI: http://frojd.se
 * Description: Plugin with functionality to add metaboxes to specific posts/pages edit view where editor can drag'n'drop articles to be added to view.
 * Version: 1.0
 * Author: Fröjd - Sara Öjelind
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\Segments;

class Segments {
    const VERSION = '1.0';

    protected $pluginSlug = 'segments';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    protected $translationDomain;

    public function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        // Set the translation domain, either the theme or plugin
        $this->translationDomain = $this->getTranslationDomain();

        // Enqueue scripts and style
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScriptsHook'));

        // For saving metabox values to post
        add_action('save_post', array($this, 'savePostHook'));

        // Action for creating segment metaboxes
        add_action('frojd_segments_metabox', array($this, 'frojdSegmentsMetaboxHook'));
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function activationHook($networkWide) {
    }

    public function deactivationHook($networkWide) {
    }

    public static function uninstallHook($networkWide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function adminEnqueueScriptsHook($page) {
        if($page == 'post.php' || $page == 'post-new.php') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script($this->pluginRelBase . '-admin-script',
                plugins_url($this->pluginRelBase . '/js/admin.js'), array('jquery'));
            wp_enqueue_style($this->pluginRelBase . '-admin-style',
                plugins_url($this->pluginRelBase . '/css/admin.css'));
        }
    }

    public function savePostHook() {
        global $post;

        if(isset($_POST['frojd_segments_metaboxes'])) {
            $screen = get_current_screen();
            $is_edit = isset($_GET['action']) && $_GET['action'] == 'edit';
            $is_post_screen = !empty($screen) && ($screen->base == 'post' || $screen->action == 'add');

            /* Make sure that the publish action is done from the post edit view and not the quick edit view */
            if($is_edit || $is_post_screen) {

                // All metaboxes are included into an array, loop through it to get the id's and save the list
                $metaboxes = array();
                foreach($_POST['frojd_segments_metaboxes'] as $metabox) {
                    $metaboxes[] = $metabox;

                    // Get the data from the specific metabox and save
                    if(isset($_POST['frojd_segments_metabox_' . $metabox])) {
                        update_post_meta($post->ID, 'frojd_segments_metabox_' . $metabox, $_POST['frojd_segments_metabox_' . $metabox]);
                    }
                }
                update_post_meta($post->ID, 'frojd_segments_metaboxes', $metaboxes);
            }
        }
    }

    /*
     * Action hook for adding segments metabox to edit view
     */
    public function frojdSegmentsMetaboxHook($metaboxes) {
        foreach($metaboxes as $id => $metabox) {
            $postTypes = array('page');
            if(isset($metabox['show_on']['post_types'])) {
                $postTypes = $metabox['show_on']['post_types'];
            }

            $is_valid_terms = (isset($metabox['show_on']['valid_terms']) && $this->checkValidTerms($metabox['show_on']['valid_terms'])) || !isset($metabox['show_on']['valid_terms']);
            $is_post_meta = (isset($metabox['show_on']['post_meta']) && $this->checkPostMeta($metabox['show_on']['post_meta'])) || !isset($metabox['show_on']['post_meta']);

            if($is_valid_terms && $is_post_meta) {
                foreach($postTypes as $postType) {
                    add_meta_box(
                        $id,
                        isset($metabox['title']) ? $metabox['title'] : 'Select articles',
                        array($this, 'metaboxArticles'),
                        $postType,
                        'normal',
                        'low',
                        $metabox
                    );
                }
            }
        }
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    /*
     * The callback function to be used when adding meta box from another location
     */
    public function metaboxArticles($post, $metabox) {
        $override = array();
        if(isset($metabox['args']['selection'])) {
            $override = $metabox['args']['selection'];
        }
        // Get the avialable articles to be able to choose from
        $availableArticles = $this->getArticles($post->ID, $override);

        // Get the current metabox data and merge with other data
        $options = get_post_meta($post->ID, 'frojd_segments_metabox_' . $metabox['id'], true);
        $segment = json_decode($options);

        $currentArticles = array();
        if(!empty($segment->posts)) {
            $currentArticles = $segment->posts;
        }

        $templateVars = array(
            'postId'            => $post->ID,
            'metabox'           => $metabox['id'],
            'title'             => !empty($segment->title) ? $segment->title : '',
            'order'             => !empty($segment->order) ? $segment->order : '0',
            'postType'          => get_post_type($post->ID),
            'options'           => $options,
            'availableArticles' => $availableArticles,
            'currentArticles'   => $currentArticles
        );
        $this->renderTemplate('admin-metabox', $templateVars);
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    /*
     * Returns a list of posts using default values and overriding when neccessary
     */
    private function getArticles($postId, $override = array()) {
        $postTypes = array('post');

        // The following args will be overwritten by the override variable
        $default = array(
            'post_type'     => $postTypes,
            'orderby'       => 'title',
            'order'         => 'ASC',
            'numberposts'   => -1,
            'tax_query'     => array('relation' => 'OR')
        );
        $args = array_merge($default, $override);
        
        $posts = get_posts($args);
        if($posts) {
            return $posts;
        }
        return false;
    }

    private function checkValidTerms($args) {
        $id = get_the_ID();
        foreach($args as $terms) {
            foreach($terms as $taxonomy => $term) {
                if(!has_term($term, $taxonomy, $id)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function checkPostMeta($args) {
        $id = get_the_ID();
        foreach($args as $post_meta) {
            foreach($post_meta as $key => $value) {
                if(get_post_meta($id, $key, true) != $value) {
                    return false;
                }
            }
        }
        return true;
    }

    private function renderTemplate($name, $vars = array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase . '/templates/' . $name . '.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of template failed</p>';
        }
    }

    private function getTranslationDomain() {
        if(function_exists('get_translation_domain')) {
            return get_translation_domain();
        }
        return $this->pluginRelBase;
    }
}

Segments::getInstance();
