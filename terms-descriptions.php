<?php
/*
Plugin Name: Terms Descriptions
Plugin URI: https://simplecoding.org/plagin-wordpress-terms-descriptions
Description: This plugin allows you to create list of terms and assign links to them. Plugin automatically replaces terms occurrences in your posts with appropriate links. You can control the number of replacements. After activation you can create terms list on plugin administration page (Tools -> Terms Descriptions).
Version: 3.3.1
Author: Vladimir Statsenko
Author URI: https://simplecoding.org
License: GPLv3
*/

/*
== Description ==

The main purpose of this plugin is easy link building.

For example, you can create a page or post with detail description of some term. Most likely, this term is used in other posts and it would be appropriate to put a links from that posts to you page. But doing this operation manually is very time consuming task.

This WordPress plugin allow you to maintain a list of terms and assign links to them. Plugin automatically replaces terms occurrences in posts with appropriate links. You can control the number of terms replacements.

This plugin allows.

* Create, update and delete terms through admin interface (Terms menu). Each term can contain one or several word forms (e.g. pear, pears).
* Assign a link to a term. This link can point to your blog post, page, custom post type or to a page on another site.
* Control how many terms will be converted to links in each post.
* Select where you want to replace terms, in posts content, in posts comments or both.
* Set several word forms for each term. For example, "apple|apples". Word forms should be separated with a "|" symbol.
* Search for terms in posts content and convert them to appropriate links. This task is performed automatically when plugin is activated.
* Export and import terms and plugin options.
* Packet terms creation.
* Permalinks update function (use it after permalinks structure updates).

Please, note that terms *will not* be replaced with links in following cases.

* If the term is already inside the link. E.g. if the link was created manually. So the plugin will not change any links that already exists in the post.
* If the term is located inside html tag. For example, inside `alt` attribute of `img` tag.
* If the term is located in `h1`-`h6` tags.
* If the term is part of another word. E.g. term = `car` and the word is `carrot`.

**Important!**

Huge terms lists with hundreds of terms can increase page creation time. In such cases, consider to use caching plugin.

Detail description of this plugin is available in [English]( https://simplecoding.org/wordpress-easy-cross-linking-with-terms-descriptions-plugin "WordPress: easy cross-linking with Terms Descriptions plugin").

Detail description of this plugin is available in [Russian]( https://simplecoding.org/plagin-wordpress-terms-descriptions "Terms Descriptions WordPress Plugin").

Serbo-Croatian translations were created by [Borisa Djuraskovic]( http://www.webhostinghub.com "Borisa Djuraskovic").

**Development**

If you want to participate in the plugin development, create a pull request to the [official GutHub repository](https://github.com/vladimir-s/terms-descriptions "Terms Descriptions GitHub repository").

== Installation ==

1. Download the zip file
2. Extract `terms-descriptions` folder
3. Upload `terms-descriptions` folder to your` wp-content/plugins` directory
4. Log in to your WordPress blog
5. Click on "Plugins"
6. Locate the "Terms Descriptions" plugin and click "Activate"
7. Go to "Term" > "Terms" to create your list of terms
8. Update DB message may appear after plugin upgrade at the top of admin pages. In this case backup you database and press "Update DB" button.
9. Go to "Terms" > "Options" and select parser type. Simple parser will search for exact terms. Simple parser with quotes support will search for terms that may be surrounded with quotes.
10. If you change permalinks structure go to "Term" > "Terms" page and press "Update permalinks" button
*/

if ( !function_exists( 'add_action' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

if ( function_exists( 'add_action' ) ) {
    // plugin definitions
    define( 'TD_DIR', plugin_dir_path( __FILE__ ) );
    define( 'TD_URL', plugins_url( '', __FILE__ ) );
    define( 'TD_FILE', __FILE__ );
}

require_once( TD_DIR . '/includes/td_options.php' );
if ( is_admin() ) {
	require_once( TD_DIR . '/includes/td_admin_terms.php' );
	require_once( TD_DIR . '/includes/td_admin_options.php' );
	require_once( TD_DIR . '/includes/td_admin_tools.php' );
	require_once( TD_DIR . '/includes/td_meta_box.php' );
	require_once( TD_DIR . '/ajax/td_terms_ajax.php' );
}
else {
    require_once( TD_DIR . '/includes/parsers/td_parser.php' );
    require_once( TD_DIR . '/includes/parsers/td_simple_parser.php' );
    require_once( TD_DIR . '/includes/parsers/td_simple_quotes_parser.php' );
    require_once( TD_DIR . '/includes/parsers/td_long_terms_first_parser.php' );
    require_once( TD_DIR . '/includes/td_frontend.php' );
}