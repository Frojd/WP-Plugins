<?php
/**
 *
 * @package Fröjd - Google Anaytics
 * @author Fröjd - Sanna Frese
 * @license Fröjd Interactive AB (All Rights Reserved).
 * @link http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Fröjd - Google Analytics
 * Plugin URI: http://frojd.se
 * Description: Google Analytics insert with input field in General Settings.
 * Version: 1.1
 * Author: Fröjd - Sanna Frese
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 *
 */

namespace Frojd\Plugins\GoogleAnalytics;

class GoogleAnalytics {
    const VERSION = '1.2';
    const TRANSLATION_SLUG = 'frojd-google_analytics';
    const TRACKING_CODE_META = 'ga-tracking_code';

    protected $pluginSlug = 'frojd-google_analytics';
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

        add_filter('admin_init', array(&$this, 'adminInitHook'));
        add_action('wp_head', array(&$this, 'renderGAOutput'));
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

    public function adminInitHook() {
        $this->registerGASettings();
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function registerGASettings() {
        add_settings_field('ga-tracking_code',
            '<label for="'.self::TRACKING_CODE_META.'">'.__('GA Tracking Code', self::TRANSLATION_SLUG).'</label>',
            array(&$this, 'trackingCodeField'), 'general');

        register_setting('general', self::TRACKING_CODE_META, 'esc_attr');
    }

    public function trackingCodeField() {
        $code = get_option(self::TRACKING_CODE_META, '');
        echo '<input type="text" id="ga-tracking_code" name="'.self::TRACKING_CODE_META.'" value="'.$code.'" />';
    }

    public function renderGAOutput() {
        $code = get_option(self::TRACKING_CODE_META);

        if (empty($code)) {
            return;
        }

        $this->renderTemplate('embed', array(
            'code' => $code)
        );
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

GoogleAnalytics::getInstance();
