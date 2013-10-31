<?php
/**
 * WPQuery Shortcode.
 * Use the [wqs][/wqs] shortcode as a wrapper for the WP_Query object.
 *
 * @package   WPQueryShortcode
 * @author    Russ Porosky <russ@indyarmy.com>
 * @license   MIT
 * @link      http://indyarmy.com/wpquery-shortcode/
 * @copyright 2013 IndyArmy Network, Inc.
 */

/**
 * Default filters for WQS shortcode callbacks.
 * These filters will wrap the entire output in a DIV, uses H3 for the header text (if any), and each item in the loop
 * is an LI within a UL. If they are selected to be shown via shortcode parameters, the thumbnail (if any), excerpt and
 * date will all be wrapped with their own SPAN tags.
 *
 * @package WPQueryShortcode
 * @author  Russ Porosky <russ@indyarmy.com>
 */
class WQSDefaults {
	/**
	 * List of default filters in this class.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	static private $filters = array(
		array(
			'name' => 'wqs_wrapper_start',
			'method' => 'wqs_wrapper_start',
			'priority' => 999,
			'args' => 1
		),
		array(
			'name' => 'wqs_wrapper_end',
			'method' => 'wqs_wrapper_end',
			'priority' => 999,
			'args' => 1
		),
		array(
			'name' => 'wqs_header',
			'method' => 'wqs_header',
			'priority' => 999,
			'args' => 1
		),
		array(
			'name' => 'wqs_pre_results',
			'method' => 'wqs_pre_results',
			'priority' => 999,
			'args' => 2
		),
		array(
			'name' => 'wqs_post_results',
			'method' => 'wqs_post_results',
			'priority' => 999,
			'args' => 1
		),
		array(
			'name' => 'wqs_pre_item',
			'method' => 'wqs_pre_item',
			'priority' => 999,
			'args' => 2
		),
		array(
			'name' => 'wqs_post_item',
			'method' => 'wqs_post_item',
			'priority' => 999,
			'args' => 1
		),
		array(
			'name' => 'wqs_show_thumb',
			'method' => 'wqs_show_thumb',
			'priority' => 999,
			'args' => 3
		),
		array(
			'name' => 'wqs_show_excerpt',
			'method' => 'wqs_show_excerpt',
			'priority' => 999,
			'args' => 3
		),
		array(
			'name' => 'wqs_show_date',
			'method' => 'wqs_show_date',
			'priority' => 999,
			'args' => 3
		),
		array(
			'name' => 'wqs_result_empty',
			'method' => 'wqs_result_empty',
			'priority' => 999,
			'args' => 1
		)
	);

	/**
	 * Install the default filters.
	 *
	 * @since 1.1.0
	 */
	public static function install_filters() {
		foreach (self::$filters as $filter) {
			if (!has_filter($filter['name'], array('WQSDefaults', $filter['method']))) {
				add_filter($filter['name'], array('WQSDefaults', $filter['method']), $filter['priority'], $filter['args']);
			}
		}
	}

	/**
	 * Remove a single default filter.
	 *
	 * @since 1.1.0
	 * @param string $name
	 */
	public static function remove_filter($name = '') {
		foreach (self::$filters as $filter) {
			if (strtolower($filter['name']) == trim(strtolower($name))) {
				if (has_filter($filter['name'], array('WQSDefaults', $filter['method']))) {
					remove_filter($filter['name'], array('WQSDefaults', $filter['method']), $filter['priority']);
				}
			}
		}
	}

	/**
	 * Remove all default filters.
	 * NOTE: Without any filters, this plugin probably doesn't do much for you. Unless, of course, you're using it to
	 * write another plugin with :)
	 *
	 * @since 1.1.0
	 */
	public static function remove_all_filters() {
		foreach (self::$filters as $filter) {
			if (has_filter($filter['name'], array('WQSDefaults', $filter['method']))) {
				remove_filter($filter['name'], array('WQSDefaults', $filter['method']), $filter['priority']);
			}
		}
	}

	/**
	 * Wrap the whole output with a DIV tag.
	 *
	 * @since 1.0.0
	 * @param string $class The class parameter you should probably use on your output wrapper.
	 *
	 * @return string The opening wrapper HTML (or whatever).
	 */
	public static function wqs_wrapper_start($class) {
		return "<div class=\"{$class}\">";
	}

	/**
	 * Wrap the whole output with a DIV tag.
	 *
	 * @since 1.0.0
	 * @return string The closing wrapper HTML (or whatever).
	 */
	public static function wqs_wrapper_end() {
		return "</div>";
	}

	/**
	 * Spit out an H3 wrapper header (if there is one).
	 *
	 * @since 1.0.0
	 * @param string $header The text between the [wqs][/wqs] tags.
	 *
	 * @return string The HTML (or whatever) that can be used as a heading for the output.
	 */
	public static function wqs_header($header) {
		if ($header) {
			return "<h3>{$header}</h3>";
		}
	}

	/**
	 * Wrap the loop output with a UL tag.
	 *
	 * @since 1.0.0
	 * @param string $class     The class parameter you should probably use on your output wrapper.
	 * @param string $post_type The post type requested by the shortcode (the item post type may be different in the future).
	 *
	 * @return string The opening loop wrapper HTML (or whatever), below the header.
	 */
	public static function wqs_pre_results($class, $post_type) {
		return "<ul class=\"{$class}_{$post_type}_wrap\">";
	}

	/**
	 * Wrap the loop output with a UL tag.
	 *
	 * @since 1.0.0
	 * @return string The closing loop wrapper HTML (or whatever).
	 */
	public static function wqs_post_results() {
		return "</ul>";
	}

	/**
	 * Wrap each loop result with an LI tag.
	 *
	 * @since 1.0.0
	 * @param string $class     The class parameter you should probably use on your result output wrapper.
	 * @param string $post_type The actual post type of the item (may be different than the requested post type in the future).
	 *
	 * @return string The HTML (or whatever) that goes immediately before each item.
	 */
	public static function wqs_pre_item($class, $post_type) {
		return "<li class=\"{$class}_{$post_type}_item\">";
	}

	/**
	 * Wrap each loop result with an LI tag.
	 *
	 * @since 1.0.0
	 * @return string The closing item wrapper HTML (or whatever).
	 */
	public static function wqs_post_item() {
		return "</li>";
	}

	/**
	 * If there's a featured image, wrap it with a SPAN tag and send it back.
	 *
	 * @since 1.0.0
	 * @param string $thumb   For now, this is a NULL string.
	 * @param int    $post_id The ID of the current item.
	 * @param string $class   The class parameter you should probably use around the thumbnail.
	 *
	 * @return string The HTML (or whatever) representing the featured image - you are responsible for calling `get_the_post_thumbnail($post_id, "thumbnail")`.
	 */
	public static function wqs_show_thumb($thumb, $post_id, $class) {
		if (has_post_thumbnail($post_id)) {
			$thumb .= "<span class=\"{$class}_wqs_thumb\">" . get_the_post_thumbnail($post_id, "thumbnail") . "</span>";
		}
		return $thumb;
	}

	/**
	 * Wrap the excerpt in a SPAN tag.
	 *
	 * @since 1.0.0
	 * @param string $excerpt The excerpt for the item.
	 * @param int    $post_id The ID of the current item.
	 * @param string $class   The class parameter you should probably use around the excerpt.
	 *
	 * @return string The HTML (or whatever) representing the excerpt.
	 */
	public static function wqs_show_excerpt($excerpt, $post_id, $class) {
		return "<span class=\"{$class}_wqs_excerpt\">{$excerpt}</span>";
	}

	/**
	 * Wrap the item publish date in a SPAN tag.
	 *
	 * @since 1.0.0
	 * @param string $date    The date the item was published, in the default WP date format.
	 * @param int    $post_id The ID of the current item.
	 * @param string $class   The class parameter you should probably use around the date.
	 *
	 * @return string The HTML (or whatever) representing the date string.
	 */
	public static function wqs_show_date($date, $post_id, $class) {
		return "<span class=\"{$class}_wqs_date\">{$date}</span>";
	}

	/**
	 * Wrap the empty message with P tags.
	 *
	 * @since 1.0.0
	 * @param string $message The message to display if no items were found.
	 *
	 * @return string The HTML (or whatever) representing the "No Posts Found" message string.
	 */
	public static function wqs_result_empty($message) {
		return "<p>{$message}</p>";
	}
}
