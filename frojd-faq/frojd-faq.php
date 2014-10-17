<?php
/**
 *
 * @package   Fröjd - FAQ
 * @author    Fröjd - Martin Sandström
 * @license   MIT
 * @link      http://frojd.se
 * @copyright 2013 Fröjd
 *
 * Plugin Name: Fröjd - FAQ
 * Plugin URI: http://frojd.se
 * Description: Creates a FAQ type that is managed through categories.
 * Version: 1.0
 * Author: Fröjd - Martin Sandström
 * Author URI: http://frojd.se
 * License: MIT
 */

namespace Frojd\Plugin\Faq;


class Faq {
    const VERSION = '1.0';

    protected $pluginSlug = 'faq';
    protected static $instance = null;

    protected $postName = 'FAQ';
    protected $postType = 'faq_post_type';
    protected $categoryTax = 'faq_category';

    protected $pluginBase;
    protected $pluginRelBase;

    protected $requirements = array();

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

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

    public function activationHook($networkWide) {
        $this->checkRequirements();
    }

    public function deactivationHook($networkWide) {
    }

    public static function uninstallHook($networkWide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function initHook() {
        $this->registerCpt();
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function checkRequirements() {
        foreach ($this->requirements as $requirement) {
            if (! is_plugin_active($requirement[1])) {
                $message = sprintf('Missing plugin: %s', $requirement[0]);
                wp_die($message);
            }
        }
    }


    private function registerCpt() {
        // Register post type
        $labels = array(
            'name' => __($this->postName, $this->pluginSlug),
            'singular_name' => __($this->postName, $this->pluginSlug),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        );

        register_post_type($this->postType, $args);

        // Register taxonomy
        $taxLabels = array(
            'name' => __('FAQ Category', $this->pluginSlug),
            'singular_name' => __('FAQ Category', $this->pluginSlug)
        );

        $taxArgs = array('hierarchical' => true,
            'labels' => $taxLabels,
            'show_ui' => true,
            'query_var' => true,
        );

        register_taxonomy($this->categoryTax, array($this->postType), $taxArgs);
    }

    private function renderTemplate($name, $vars = array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of admin template failed</p>';
        }
    }
}

Faq::getInstance();
