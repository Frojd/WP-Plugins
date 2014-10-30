<?php
/**
 *
 * @package   Frojd - Splash
 * @author    Fröjd - Carl Klingberg, Andreas Hultgren
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013-2014 Fröjd
 *
 * Plugin Name: Frojd &mdash; Splash
 * Plugin URI: http://frojd.se
 * Description: Custom post type for splash content
 * Version: 1.0.0
 * Author: Fröjd - Carl Klingberg, Andreas Hultgren
 * Author URI: http://frojd.se
 * License: GPLv2 or later
 */

namespace Frojd\Plugins\Splash;

require_once 'lib/assets.php';
require_once 'lib/metabox.php';
require_once 'lib/request.php';
require_once 'lib/utils.php';
require_once 'lib/view.php';

class Splash {
    const VERSION = '1.0.0';
    const TRANSLATION_SLUG = 'frojd_splash';

    protected static $instance = null;

    protected $plugin_base;
    protected $plugin_rel_base;

    protected $posttype_slug = 'frojd_splash';

    private function __construct() {
        $this->plugin_base = rtrim(dirname(__FILE__), '/');
        $this->plugin_rel_base = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activation_hook'));
        register_activation_hook(__FILE__, array(&$this, 'deactivation_hook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstall_hook'));

        $this->assets = new Lib\Assets(array(
            'hook' => 'admin_enqueue_scripts',
            'scripts' => array(
                'jquery-ui-sortable' => '',
                'frojd-splash' => 'frojd-splash.js',
            ),
            'styles' => array(
                'frojd-splash' => 'frojd-splash.css',
            )
        ));

        add_action('init', array($this, 'initHook'));
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

    public function activation_hook($network_wide) {
    }

    public function deactivation_hook($network_wide) {
    }

    public static function uninstall_hook($network_wide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function initHook() {
        $this->registerCpt();
        $this->addSelectionMetabox();
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function getTheSplashes($id=null) {
        $splashIds = $this->getTheSplashIds($id);

        return get_posts(array(
            'post_type' => $this->posttype_slug,
            'post__in' => $splashIds,
            'orderby' => 'post__in',
        ));
    }

    public function getTheSplashIds($id=null) {
        global $post;

        if (!$id) {
            $id = $post->ID;
        }

        $splashIds = explode(',', get_post_meta($id, 'selected_splashes', true));

        return $splashIds;
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function registerCpt() {
        $args = array(
            'labels' => array(
                'name' => __('Splash', self::TRANSLATION_SLUG),
                'singular_name' => __('Splash', self::TRANSLATION_SLUG),
                'menu_name' => __('Splashes', self::TRANSLATION_SLUG),
            ),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'supports' => array('title', 'editor', 'thumbnail')
        );

        register_post_type($this->posttype_slug, $args);
    }

    private function addSelectionMetabox() {
        $this->selectionMetabox = new Lib\Metabox(array(
            'id' => 'splash_selection',
            'title' => __('Splash', self::TRANSLATION_SLUG),
            'template' => 'metabox-select',
            'screens' => array('page'),
            'context' => 'normal',
            'fields' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'selected_splashes',
                    'key' => 'selected_splashes',
                    'data-attr' => 'data-splash_selection_store',
                ),
            ),
            'data' => array(
                'post_type' => $this->posttype_slug
            )
        ));
    }
}

Splash::getInstance();
