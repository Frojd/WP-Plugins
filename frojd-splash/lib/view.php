<?php

namespace Frojd\Plugins\Splash\Lib;

/**
 * Utility class for rendering templates
 */
class View {
    static $root;

    public static function render($template, $data=array()) {
        foreach ($data as $key => $val) {
            $$key = $val;
        }

        $template_path = self::$root.$template.'.php';

        if (file_exists($template_path)) {
            include($template_path);
        } else {
            echo '<p>Rendering of template '. $template . ' failed</p>';
        }
    }
}

// Set complex static var hack
View::$root = __DIR__ . '/../templates/';
