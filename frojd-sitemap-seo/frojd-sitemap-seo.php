<?php
/**
 *
 * @package   Fröjd - Sitemap & SEO
 * @author    Fröjd
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB
 *
 * Plugin Name: Fröjd - Sitemap & SEO
 * Plugin URI: http://frojd.se
 * Description: This plugin allows you to edit the robots.txt file and choose which post types to be included in the sitemap. It also allows you to specify a couple of seo fields, such as site description and keywords.
 * Version: 1.2.2
 * Author: Fröjd
 * Author URI: http://frojd.se
 * License: Fröjd Interactive AB (All Rights Reserved).
 */

class SitemapSeo {
    const VERSION = '1.2.1';
    const SEO_KEYWORDS_FIELD = 'seo_post_keywords';

    protected $pluginSlug = 'sitemap_seo';
    protected static $instance = null;

    protected $supportedTypes = array("post", "page");
    protected $pluginBase;
    protected $pluginRelBase;


    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase= dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_activation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        add_action('plugins_loaded', array( $this, 'pluginsLoadedHook'));
        add_action('admin_init', array($this, 'adminInitHook'));
        add_action('init', array($this, 'initHook'));
        add_filter('generate_rewrite_rules',
            array($this, 'generateRewriteRulesHook'));
        add_action('parse_request', array($this, 'parseRequestHook'));
        add_action('do_feed_sitemap', array($this, 'doFeedSitemapHook'), 10, 1 );
        add_action('do_meta_boxes', array($this, 'doMetaBoxesHook'));
        add_action('save_post', array($this, 'savePostHook'));
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

    public function pluginsLoadedHook() {
        $this->initTextdomain();
    }

    public function adminInitHook() {
        $this->registerSeoDescriptionSettings();
        $this->registerRobotSettings();
    }

    public function initHook() {
        $this->flushRules();
    }

    public function generateRewriteRulesHook() {
        global $wp_rewrite;

        $feedRules = array(
            '.*sitemap.xml$' => 'index.php?feed=sitemap'
        );

        $wp_rewrite->rules = $feedRules + $wp_rewrite->rules;
    }

    public function parseRequestHook() {
        global $wp;

        if (preg_match('/robots.txt$/i', $wp->request)) {
            $robots = get_option('robots_txt', '');
            die($robots);
        }
    }

    public function doFeedSitemapHook() {
        $this->renderTemplate('sitemap-template');
    }

    public function doMetaBoxesHook() {
        foreach ($this->supportedTypes as $screen) {
            add_meta_box(
                self::SEO_KEYWORDS_FIELD,
                __('SEO keywords', 'sitemap-seo'),
                array($this, 'seoPostKeywordsMetabox'),
                $screen,
                'normal',
                'default'
            );
        }
    }

    public function savePostHook() {
        global $post;

        if (! in_array($post->post_type, $this->supportedTypes)) {
            return;
        }

        if (isset($_POST[self::SEO_KEYWORDS_FIELD])) {
            update_post_meta($post->ID, self::SEO_KEYWORDS_FIELD,
                $_POST[self::SEO_KEYWORDS_FIELD]);
        }
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function registerSeoDescriptionSettings() {
        // Add the section to reading settings so we can add our
        // fields to it
        add_settings_section('seo_setting_section',
            'SEO settings',
            array($this, 'seoSettingSectionCallback'),
            'general');

        // Add the field with the names and function to use for our new
        // settings, put it in our new section
        add_settings_field('seo_setting_description',
            'Site description',
            array($this, 'seoSettingDescriptionCallback'),
            'general',
            'seo_setting_section');

        add_settings_field('seo_setting_keywords_enabled',
            'Enable keywords',
            array($this, 'seoSettingKeywordsEnabledCallback'),
            'general',
            'seo_setting_section');

        add_settings_field('seo_setting_keywords',
            'Keywords',
            array($this, 'seoSettingKeywordsCallback'),
            'general',
            'seo_setting_section');

        // Register our setting so that $_POST handling is done for us and
        // our callback function just has to echo the <input>
        register_setting('general', 'seo_setting_description');
        register_setting('general', 'seo_setting_keywords_enabled');
        register_setting('general', 'seo_setting_keywords');
    }

    public function registerRobotSettings() {
        register_setting(
            'reading',
            'robots_txt',
            'esc_attr'
        );

        add_settings_field(
            'robots_txt',
            '<label for="robots_txt">'.__('Robots.txt', 'sitemap-seo' ).'</label>',
            array($this, 'robotsTxtFieldsHtml'),
            'reading'
        );

        register_setting(
            'reading',
            'sitemap_settings',
            array($this, 'convertSettingFormat')
        );

        add_settings_field('sitemap_settings',
            '<label for="sitemap_settings">'
            .__('Sitemap settings', 'sitemap-seo').'</label>',
            array($this, 'robotsTxtSitemapSettingsHtml'),
            'reading'
        );
    }

    public function convertSettingFormat() {
        if (! isset($_POST['sitemap_settings'])) {
            return "";
        }

        $sitemapSettings = implode(',', $_POST['sitemap_settings']);
        return $sitemapSettings;
    }

    public function robotsTxtSitemapSettingsHtml() {
        $sitemapPostTypes = get_option('sitemap_settings', '');
        $sitemapPostTypes = explode(',', $sitemapPostTypes);
        $postTypes = get_post_types(array( 'show_ui' => true ), 'objects');

        echo '<select id="sitemap_settings" name="sitemap_settings[]" multiple>';
        foreach($postTypes as $postType) {
            $selected = false;

            if (array_search($postType->name, $sitemapPostTypes, true) !== false) {
                $selected = true;
            }

            echo '<option value="'.$postType->name.'"'
                .($selected ? 'selected' : '').'>'
                .$postType->labels->name
                .'</option>';
        }
        echo '</select>';
    }

    public function robotsTxtFieldsHtml() {
        $value = get_option( 'robots_txt', '' );
        echo '<textarea rows="10" cols="100" id="robots_txt" name="robots_txt">'
            .$value
            .'</textarea>';
    }

    public function seoSettingSectionCallback() {
        echo '<p>Site description is used as meta description on the front page.</p>';
    }

    public function seoSettingDescriptionCallback() {
        echo sprintf('<textarea name="seo_setting_description"
            id="seo_setting_description" class="large-text code">%s</textarea>',
            get_option('seo_setting_description')
        );
    }

    public function seoSettingKeywordsEnabledCallback() {
        echo sprintf('<input type="checkbox" name="seo_setting_keywords_enabled"
            id="seo_setting_keywords_enabled" value="1" %s>',
            (get_option('seo_setting_keywords_enabled') ? ' checked' : '')
        );
    }

    public function seoSettingKeywordsCallback() {
        echo sprintf('<textarea name="seo_setting_keywords"
            id="seo_setting_keywords" class="large-text code">%s</textarea>',
            get_option('seo_setting_keywords')
        );
    }

    public function seoPostKeywordsMetabox() {
        global $post;

        $value = get_post_meta($post->ID, self::SEO_KEYWORDS_FIELD, true);

        echo sprintf('<textarea id="%1$s" name="%1$s"
            class="custom-text-field large-text">%2$s</textarea>',
            self::SEO_KEYWORDS_FIELD,
            $value
        );
   }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function initTextdomain() {
        load_plugin_textdomain($this->pluginSlug, false,
            $this->pluginRelBase.'/langs/');
    }

    private function flushRules() {
        $rules = get_option('rewrite_rules');
        if (!isset($rules['.*sitemap.xml$'])) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }

    private function renderTemplate($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }

        $templatePath = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($templatePath)) {
            include($templatePath);
        } else {
            echo '<p>Rendering of template failed</p>';
        }
    }
}

SitemapSeo::getInstance();

