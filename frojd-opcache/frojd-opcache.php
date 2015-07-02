<?php
/**
 *
 * @package Fröjd - OpCache
 * @author Fröjd - Sara Öjelind
 * @license Fröjd Interactive AB (All Rights Reserved).
 * @link http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Fröjd - OpCache
 * Plugin URI: http://frojd.se
 * Description: Reset button for OpCache
 * Version: 1.0
 * Author: Fröjd - Sara Öjelind
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 *
 */

namespace Frojd\Plugins\FrojdOpCache;

class FrojdOpCache {
    const VERSION = '1.0';
    const TRANSLATION_SLUG = 'frojd-opcache';
    const SETTING_NAME = 'frojd_opcache';

    protected $pluginSlug = 'frojd-opcache';
    protected static $instance = null;

    // Absolute path to plugin dir
    protected $pluginBase;
    protected $pluginRelBase;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_deactivation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        // Only show settings page if opcache exists
        if(function_exists('opcache_get_status')) {
            $opcache = opcache_get_status();
            if(isset($opcache['opcache_enabled']) && $opcache['opcache_enabled']) {
                add_action('admin_menu', array($this, 'adminMenuHook'));
            }
        }
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

    public function activationHook($network_wide) {
    }

    public function deactivationHook($network_wide) {
    }

    public static function uninstallHook($network_wide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function adminMenuHook() {
        add_management_page(
            __('Fröjd OpCache', self::TRANSLATION_SLUG),
            __('Fröjd OpCache', self::TRANSLATION_SLUG),
            'manage_options',
            self::SETTING_NAME,
            array($this, 'settingsPage')
        );
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function settingsPage() {
        $this->renderTemplate('settings');
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    public function renderTemplate($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $path = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($path)) {
            include($path);
        } else {
            echo '<p>Rendering of template failed</p>';
        }
    }
}

FrojdOpCache::getInstance();
