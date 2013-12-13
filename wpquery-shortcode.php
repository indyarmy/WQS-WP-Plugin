<?php
/**
 * WPQuery Shortcode.
 *
 * Use the [wqs][/wqs] shortcode as a wrapper for the WP_Query object.
 *
 * @package   WPQueryShortcode
 * @author    Russ Porosky <russ@indyarmy.com>
 * @license   MIT
 * @link      http://indyarmy.com/wpquery-shortcode/
 * @copyright 2013 IndyArmy Network, Inc.
 *
 * @wordpress-plugin
 * Plugin Name: WPQuery Shortcode
 * Plugin URI:  http://indyarmy.com/wpquery-shortcode/
 * Description: A WPQuery shortcode wrapper.
 * Version:     1.1.2
 * Author:      Russ Porosky <russ@indyarmy.com>
 * Author URI:  http://indyarmy.com/
 * Text Domain: wqs
 * Domain Path: /lang
 * License:     MIT
 * License URI: http://opensource.org/licenses/MIT
 */

if (!defined("WPINC")) {
	die();
}

require_once(plugin_dir_path(__FILE__) . "class-wqs.php");

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook(__FILE__, array("WQS", "activate"));
register_deactivation_hook(__FILE__, array("WQS", "deactivate"));

WQS::get_instance();
