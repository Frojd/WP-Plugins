=== Fröjd - Google Analytics ===
Contributors: Sanna Frese
Requires at least:
Tested up to: 4.0
Stable tag: Trunk
License: Fröjd Interactive AB (All Rights Reserved)
License URI: http://frojd.se

== Description ==

Creates option to enter Google Analytics Tracking Code under General Settings, and outputs basic Google Analytics-script with same GA Tracking Code in header.

== Installation ==

1. Upload ‘frojd-google_analytics.php' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add your GA Tracking Code under Settings -> General

== Changelog ==

= 1.2 =
* Created a constant for code meta key.
* Added hook method for admin init.
* Updated template rendering.
* Simplified flow in render method.
* Fixed issue where write context caused problems on php 5.4.

= 1.1 =
* Changed name of plugin, moved script output to separate template file. Plugin now only outputs script if input field has value.

= 1.0 =
* Plugin now outputs basic Google Analytics-script with GA Tracking Code from input in General Settings.

= 0.03 =
* Added readme.txt

= 0.02 =
* Added field for GA Tracking code

= 0.01 =
* Plugin base
