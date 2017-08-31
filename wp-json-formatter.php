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

$formatter = new ShortlistMedia\WpJsonFormatter\JsonFormatter;
$formatter->register();
