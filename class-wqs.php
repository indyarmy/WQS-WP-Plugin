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
 */

/**
 * Plugin class.
 *
 * @package WPQueryShortcode
 * @author  Russ Porosky <russ@indyarmy.com>
 */
class WQS {
	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $version = "1.0.0";

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_slug = "wqs";

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance = NULL;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Load plugin text domain.
		add_action("init", array($this, "load_plugin_textdomain"));

		// Activate plugin when new blog is added .
		add_action("wpmu_new_blog", array($this, "activate_new_site"));

		// Add shortcodes.
		add_shortcode("wqs", array($this, "wqs"));

		// Add default filters.
		if (!has_filter("wqs_wrapper_start", array("WQSDefaults", "wqs_wrapper_start"))) {
			add_filter("wqs_wrapper_start", array("WQSDefaults", "wqs_wrapper_start"), 999);
		}
		if (!has_filter("wqs_wrapper_end", array("WQSDefaults", "wqs_wrapper_end"))) {
			add_filter("wqs_wrapper_end", array("WQSDefaults", "wqs_wrapper_end"), 999);
		}
		if (!has_filter("wqs_header", array("WQSDefaults", "wqs_header"))) {
			add_filter("wqs_header", array("WQSDefaults", "wqs_header"), 999);
		}
		if (!has_filter("wqs_pre_results", array("WQSDefaults", "wqs_pre_results"))) {
			add_filter("wqs_pre_results", array("WQSDefaults", "wqs_pre_results"), 999, 2);
		}
		if (!has_filter("wqs_post_results", array("WQSDefaults", "wqs_post_results"))) {
			add_filter("wqs_post_results", array("WQSDefaults", "wqs_post_results"), 999);
		}
		if (!has_filter("wqs_pre_item", array("WQSDefaults", "wqs_pre_item"))) {
			add_filter("wqs_pre_item", array("WQSDefaults", "wqs_pre_item"), 999, 2);
		}
		if (!has_filter("wqs_post_item", array("WQSDefaults", "wqs_post_item"))) {
			add_filter("wqs_post_item", array("WQSDefaults", "wqs_post_item"), 999);
		}
		if (!has_filter("wqs_show_thumb", array("WQSDefaults", "wqs_show_thumb"))) {
			add_filter("wqs_show_thumb", array("WQSDefaults", "wqs_show_thumb"), 999, 3);
		}
		if (!has_filter("wqs_show_excerpt", array("WQSDefaults", "wqs_show_excerpt"))) {
			add_filter("wqs_show_excerpt", array("WQSDefaults", "wqs_show_excerpt"), 999, 3);
		}
		if (!has_filter("wqs_show_date", array("WQSDefaults", "wqs_show_date"))) {
			add_filter("wqs_show_date", array("WQSDefaults", "wqs_show_date"), 999, 3);
		}
		if (!has_filter("wqs_result_empty", array("WQSDefaults", "wqs_result_empty"))) {
			add_filter("wqs_result_empty", array("WQSDefaults", "wqs_result_empty"), 999);
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if (NULL === self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate($network_wide) {
		if (function_exists("is_multisite") && is_multisite()) {
			if ($network_wide) {
				// Get all blog ids.
				$blog_ids = self::get_blog_ids();
				foreach ($blog_ids as $blog_id) {
					switch_to_blog($blog_id);
					self::single_activate();
				}
				restore_current_blog();
			} else {
				self::single_activate();
			}
		} else {
			self::single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate($network_wide) {
		if (function_exists("is_multisite") && is_multisite()) {
			if ($network_wide) {
				// Get all blog ids.
				$blog_ids = self::get_blog_ids();
				foreach ($blog_ids as $blog_id) {
					switch_to_blog($blog_id);
					self::single_deactivate();
				}
				restore_current_blog();
			} else {
				self::single_deactivate();
			}
		} else {
			self::single_deactivate();
		}
	}

	/**
	 * Fired when a new site is activated within a WPMU environment.
	 *
	 * @since 1.0.0
	 *
	 * @param int $blog_id ID of the new blog.
	 */
	public function activate_new_site($blog_id) {
		if (1 !== did_action("wpmu_new_blog")) {
			return;
		}
		switch_to_blog($blog_id);
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since 1.0.0
	 * @return array|false The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;
		// Get an array of blog ids.
		$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";
		return $wpdb->get_col($sql);
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since 1.0.0
	 */
	private static function single_activate() {
		// Currently nothing to do.
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since 1.0.0
	 */
	private static function single_deactivate() {
		// Currently nothing to do.
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters("plugin_locale", get_locale(), $domain);
		load_textdomain($domain, WP_LANG_DIR . "/" . $domain . "/" . $domain . "-" . $locale . ".mo");
		load_plugin_textdomain($domain, FALSE, basename(dirname(__FILE__)) . "/lang/");
	}

	/**
	 * Core controller for the shortcode.
	 *
	 * This method combines the passed attributes with some defaults, creates a new WP_Query object, and returns the
	 * results as a content string. Uses several filters and an action to allow theme and plugin authors to
	 * manipulate the output.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Attributes and WP_Query object attributes to use for this loop.
	 * @param string $heading Text between the shortcode tags, typically used as a heading.
	 *
	 * @return string Contents contains the results of the WP_Query call, manipulated through the filters to be a string.
	 */
	public static function wqs($atts, $heading = NULL) {
		$self = self::get_instance();
		$header = NULL;
		$content = NULL;
		$footer = NULL;
		$defaults = array(
			"class" => "wqs_list", // CSS class for output wrapper
			"show_excerpt" => FALSE, // show the post excerpt
			"show_date" => FALSE, // show the post date
			"show_thumb" => FALSE, // show the featured image, if any
			"show_empty_message" => __("Sorry, no posts were found.", $self->plugin_slug), // the message displayed if no posts were found
		);
		$wpquery = array(
			"author" => NULL,
			"author_name" => NULL,
			"cat" => NULL,
			"category_name" => NULL,
			"category__and" => array(),
			"category__in" => array(),
			"category__not_in" => array(),
			"tag" => NULL,
			"tag_id" => NULL,
			"tag__and" => array(),
			"tag__in" => array(),
			"tag__not_in" => array(),
			"tag_slug__and" => array(),
			"tag_slug__in" => array(),
			"tax_query" => array(),
			"s" => NULL,
			"p" => NULL,
			"name" => NULL,
			"page_id" => NULL,
			"pagename" => NULL,
			"post_parent" => NULL,
			"post_parent__in" => array(),
			"post_parent__not_in" => array(),
			"post__in" => array(),
			"post__not_in" => array(),
			"post_type" => "post", // only grab posts
			"post_status" => NULL,
			"ignore_sticky_posts" => FALSE,
			"order" => "DESC", // newest at the top
			"orderby" => "date", // newest at the top
			"year" => NULL,
			"monthnum" => NULL,
			"w" => NULL,
			"day" => NULL,
			"hour" => NULL,
			"minute" => NULL,
			"second" => NULL,
			"m" => NULL,
			"meta_key" => NULL,
			"meta_value" => NULL,
			"meta_value_num" => NULL,
			"meta_compare" => "=", // default to meta_key = meta_value
			"meta_query" => array(),
			"perm" => NULL,
			"cache_results" => TRUE,
			"update_post_meta_cache" => TRUE,
			"update_post_term_cache" => TRUE,
			"posts_per_page" => 5, // only use 5 results
		);
		$options = shortcode_atts(array_merge($defaults, $wpquery), $atts, $self->plugin_slug);

		// Check the following index to make sure the loop you're messing with is the right one!
		$options["_wqs_post_type"] = $options["post_type"];

		// Mess with the WP_Query attributes here.
		$options = apply_filters("wqs_pre_query", $options, $options["post_type"]);

		$query = new WP_Query($options);

		// Don't know what you'll use this for, but it's here just in case.
		do_action("wqs_post_query", $query, $options["post_type"]);

		// Wrap it, dude.
		$header = apply_filters("wqs_wrapper_start", $options["class"], $options["post_type"]);

		// Announce it!
		$header .= apply_filters("wqs_header", $heading, $options["class"], $options["post_type"]);

		if ($query->have_posts()) {
			// Wrap it twice to be sure.
			$content .= apply_filters("wqs_pre_results", $options["class"], $options["post_type"]);
			while ($query->have_posts()) {
				$query->the_post();
				$post_type = get_post_type();
				$id = get_the_ID();

				// Need to stick anything in front of each item? Like an <li> element or something?
				$content .= apply_filters("wqs_pre_item", $options["class"], $post_type);

				$content .= "<a href=\"" . get_permalink() . "\" class=\"" . $options["class"] . "_title\">" . get_the_title() . "</a>";

				if ($options["show_thumb"]) {
					$content .= apply_filters("wqs_show_thumb", "", $id, $options["class"], $post_type);
				}

				if ($options["show_excerpt"]) {
					$content .= apply_filters("wqs_show_excerpt", get_the_excerpt(), $id, $options["class"], $post_type);
				}

				if ($options["show_date"]) {
					$content .= apply_filters("wqs_show_date", get_the_date(), $id, $options["class"], $post_type);
				}

				// Close that <li> my friend.
				$content .= apply_filters("wqs_post_item", $options["class"], $post_type);
			}

			// Close your inside wrapper.
			$content .= apply_filters("wqs_post_results", $options["class"], $options["post_type"]);
		} else {
			if ($options["show_empty_message"]) {
				// No posts! Are you sure you spelled the post_type or category right?
				$content .= apply_filters("wqs_result_empty", $options["show_empty_message"], $options["class"], $options["post_type"]);
			}
		}

		// End the wrapping entirely.
		$footer = apply_filters("wqs_wrapper_end", $options["class"], $options["post_type"]);

		wp_reset_postdata();

		return $header . $content . $footer;
	}
}

// Load up the default filters.
require_once(plugin_dir_path(__FILE__) . "class-wqsdefaults.php");
