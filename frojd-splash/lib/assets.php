<?php

namespace Frojd\Plugins\Splash\Lib;

/**
 * Assets simplifies the handling of assets.
 */
class Assets {
    public $assets_dir;
    public $js_dir;
    public $css_dir;

    public $scripts = array();
    public $styles = array();

    private $disabled = false;

    public function __construct($options = array()) {
        $this->assets_dir = 'assets/';
        $this->js_dir = 'js/';
        $this->css_dir = 'css/';

        foreach ($options as $key => $value) {
            $this->$key = $value;
        }

        $hook = isset($options['hook']) ? $options['hook'] : 'wp_enqueue_scripts';
        add_action($hook, array(&$this, 'enqueueScriptsHook'));
    }

    /* Hooks
    ============================================================================= */

    public function enqueueScriptsHook() {
        if($this->disabled) {
            return;
        }

        foreach ($this->scripts as $name => $file) {
            if(!$file) {
                wp_enqueue_script($name);
            } else {
                wp_enqueue_script($name,
                    plugins_url($this->assets_dir.$this->js_dir.$file, __DIR__),
                    '',
                    '',
                    true
                );
            }
        }

        foreach ($this->styles as $name => $file) {
            wp_enqueue_style($name,
                plugins_url($this->assets_dir.$this->css_dir.$file, __DIR__)
            );
        }
    }

    /* Public
    ============================================================================= */

    public function disable() {
        $this->disabled = true;
    }

    public function enable() {
        $this->disabled = false;
    }
}
