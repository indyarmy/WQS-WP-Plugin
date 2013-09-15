# WPQuery Shortcode #

Contributors: indyarmy
Donate link: http://indyarmy.com/wpquery-shortcode/
Tags: query, wpquery, shortcode
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 1.0.0
License: MIT
License URI: http://opensource.org/licenses/MIT

A shortcode wrapper for WP_Query.

## Description ##
**WPQuery Shortcode** is a lightweight shortcode that wraps the functionality of WP_Query.

## Installation ##
1. Upload the `wpquery-shortcode` folder to your `wp-content/plugins/` folder
2. Activate the plugin

## Options ##
WQS has a few attributes that do not belong to the WP_Query class.

`class="wqs_list"` - the CSS class assigned to the `ul` element that wraps the list of posts.

`show_empty_message="Sorry, no posts were found."` - the message displayed if no posts are found.

`show_thumb=FALSE` - whether to display the post thumbnail (Featured Image), if any.

`show_excerpt=FALSE` - whether to display the post excerpt.

`show_date=FALSE` - whether to display the post date below the excerpt.

Aside from that, any parameter that can be passed to `WP_Query` can be used here, except for `tax_query` and `meta_query` (for now). The following ones have defaults.

`post_type=post` - blog posts

`posts_per_page=5` - only displays 5 posts

`orderby="date"` - order by post date

`order="DESC"` - descending order (newest first)

`meta_compare="="` - if a `meta_key` is specified, the default comparison is `=`.

## Filters & Actions ##
WQS includes a few filter and action hooks for your hacking and themeing pleasure.

### Filters ###
#### `wqs_pre_query` ####
* executed after the options have been combined
* intended to give theme authors the opportunity to mess with the query arguments, including adding advanced `tax_query` or `meta_query` arrays
* includes two parameters:
  * `$options` the complete array of attributes being passed to `WP_Query`
  * `$post_type` the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** the modified `$options` array
* **Default** the unmodified `$options` array

#### `wqs_wrapper_start` ####
* executed immediately after the call to `new WP_Query($args)`, before the Loop and before we've even checked if there are any posts returned
* intended to allow theme authors to customize the wrapper element(s)
* passed two parameters:
  * `$class` - the CSS class from the shortcode
  * `$post_type` - the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** the opening wrapper element(s)
* **Default** `<div class="$class">`

#### `wqs_header` ####
* executed immediately after the `wqs_wrapper_start` filter
* intended to allow theme authors to mess with the passed shortcode content (which is, itself, inteneded to be the header for the resulting list)
* passed three parameters:
  * `$header` the content between the `[wqs]` and `[/wqs]` tags
  * `$class` the CSS class from the shortcode
  * `$post_type` the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** a header to display above the resulting list
* **Default** `<h3>$header</h3` or `NULL` if `$header` is empty

#### `wqs_pre_results` ####
* executed before The Loop begins, but after we have confirmed there is at least one result to display
* intended to allow theme authors to customize the wrapper for the result list itself
* passed two parameters:
  * `$class` the CSS class from the shortcode
  * `$post_type` the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** an opening wrapper element for the list of results
* **Default** `<ul class="{$class}_{$post_type}_wrap">`

#### `wqs_pre_item` ####
* executed before each item in the result list
* intended to allow theme authors to customize the element that wraps each item in the list
* passed two parameters:
  * `$class` the CSS class from the shortcode
  * `$post_type` the actual post type (eg. `post`, `attachment`, `movie`)
* **Return** an opening wrapper element for the current item
* **Default** `<li class="{$class}_{$post_type}_item">`

#### `wqs_show_thumb` ####
* executed within the Loop
* passed four parameters:
  * `$thumb` - an empty string
  * `$post_id` - the ID of the current post (same as `get_the_ID()` function)
  * `$class` the CSS class from the shortcode
  * `$post_type` - the actual post type (eg. `post`, `attachment`, `movie`)
* **Return** ideally, the thumbnail
* **Default** `<span class="{$class}_wqs_thumb"><img src="…" /></span>` or `$thumb` if the call to `has_post_thumbnail($post_id)` fails

#### `wqs_show_excerpt` ####
* executed within the Loop
* passed four parameters:
  * `$excerpt` - a string containing the result of a call to `get_the_excerpt()`; should be modified and `return`ed
  * `$post_id` - the ID of the current post (same as `get_the_ID()` function)
  * `$class` the CSS class from the shortcode
  * `$post_type` - the actual post type (eg. `post`, `attachment`, `movie`)
* **Return** an excerpt for the current post
* **Default** `<span class="{$class}_wqs_excerpt">$excerpt</span>`

#### `wqs_show_date` ####
* executed within the Loop
* passed four parameters:
  * `$date` - a string containing the result of a call to `get_the_date()`; should be modified and `return`ed
  * `$post_id` - the ID of the current post (same as `get_the_ID()` function)
  * `$class` the CSS class from the shortcode
  * `$post_type` - the actual post type (eg. `post`, `attachment`, `movie`)
* **Return** a date string to display
* **Default** `<span class="{$class}_wqs_date">$date</span>`

#### `wqs_post_item` ####
* executed after each item in the result list
* intended to allow theme authors to customize the element that wraps each item in the list
* passed two parameters:
  * `$class` the CSS class from the shortcode
  * `$post_type` the actual post type (eg. `post`, `attachment`, `movie`)
* **Return** a closing wrapper element for the current item
* **Default** `</li>`

#### `wqs_post_results` ####
* executed after The Loop finishes, but only if at least one result existed
* intended to allow theme authors to customize the wrapper for the result list itself
* passed two parameters:
  * `$class` the CSS class from the shortcode
  * `$post_type` the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** a closing wrapper element for the list of results
* **Default** `</ul>`

#### `wqs_wrapper_end` ####
* executed immediately befoer the call to `wp_reset_postdata()`
* intended to allow theme authors to customize the wrapper element(s)
* passed two parameters:
  * `$class` - the CSS class from the shortcode
  * `$post_type` - the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** the closing wrapper element(s)
* **Default** `</div>`

#### `wqs_result_empty` ####
* executed after the `wqs_header` filter if there are no results
* passed three parameters:
  * `$message` the `show_empty_message` value from the shortcode
  * `$class` - the CSS class from the shortcode
  * `$post_type` - the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Return** the message to display if no posts are found
* **Default** `<p>$message</p>`

### Actions ###
#### `wqs_post_query` ####
* executed immediately after the call to `new WP_Query($args)`, before the Loop and before we've even checked if there are any posts returned
* passed two parameters:
  * `$query` - the actual `WP_Query` object
  * `$post_type` - the post type from the shortcode (eg. `post`, `attachment`, `movie`)
* **Default** there is currently no default action associated with this hook
  
## Examples ##
The most basic example is this:

	[wqs][/wqs]

Which returns the following HTML by default:

	<div class="wqs_list">
	    <ul class="wqs_list_post_wrap">
	+--     <li class="wqs_list_post_item">
	|           <a href="…" class="wqs_title">Title One</a>
	| 5x        <span class="wqs_list_wqs_excerpt">
	|               <p>This is the excerpt text.</p>
	|           </span>
	+--     </li>
	    </ul>
	</div>

## Changelog ##
**1.0.0**

* Initial release
