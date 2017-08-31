<?php
/**
* @wordpress-plugin
* Plugin Name: WP Json Formatter
* Plugin URI: http://github.com/shortlist-digital/something-here
* Description: A plugin to format the json api response for the frontend
* Version: 1.0.0
* Author: Shortlist Studio
* Author URI: http://shortlist.studio
* License: MIT
*/

// this will be removed from here when it is added to the main file
require __DIR__ . '/vendor/autoload.php';

$formatter = new Shortlist\Croissant\WpJsonFormatter\JsonFormatter;
$formatter->register();
