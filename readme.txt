=== Terms descriptions ===
Contributors: vladimir.s
Tags: post, page, links, plugin
Requires at least: 4.1
Tested up to: 4.9.8
Stable tag: trunk

This plugin allows you to create list of terms and assign links to them. Plugin replaces terms occurrences in your posts with appropriate links.

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
* Disable terms to links conversion for individual posts.
* Wrap links with arbitrary text (for example, you can wrap link with strong tag)
* Export and import terms and plugin options.
* Export terms in CSV format.
* Packet terms creation.
* Permalinks update function (use it after permalinks structure updates).

Please, note that terms *will not* be replaced with links in following cases.

* If the term is already inside the link. E.g. if the link was created manually. So the plugin will not change any links that already exists in the post.
* If the term is located inside html tag. For example, inside `alt` attribute of `img` tag.
* If the term is located in `h1`-`h6` tags.
* If the term is part of another word. E.g. term = `car` and the word is `carrot`.
* If you checked "Disable Terms Descriptions plugin for this post" checkbox (at the bottom of post edit screen).

**Important!**

Huge terms lists with hundreds of terms can increase page creation time. In such cases, consider to use caching plugin.

Detail description of this plugin is available in [English]( https://www.simplecoding.org/wordpress-easy-cross-linking-with-terms-descriptions-plugin "WordPress: easy cross-linking with Terms Descriptions plugin").

Detail description of this plugin is available in [Russian]( https://www.simplecoding.org/plagin-wordpress-terms-descriptions "Terms Descriptions WordPress Plugin").

Serbo-Croatian translations were created by [Borisa Djuraskovic]( http://www.webhostinghub.com "Borisa Djuraskovic").

**Development**

If you want to participate in the plugin development, create a pull request to the [official GutHub repository](https://github.com/vladimir-s/terms-descriptions "Terms Descriptions GitHub repository").

The plugin built with [PhpStorm]( http://www.jetbrains.com/phpstorm/ )

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
11. If you want to disable the plugin for individual posts check "Disable Terms Descriptions plugin for this post" checkbox (at the bottom of post edit screen)

== Screenshots ==

1. Terms creation page
2. Page selection autocomplete field
3. Options page
4. Tools page
5. The plugin built with PhpStorm

== Changelog ==

= 3.3.1 =

* New feature: Same URLs with different anchors are not treated as unique
* WP 4.9.8 support

= 3.3.0 =

* New feature: Custom title tag added for external links
* Bug fix: in SCO_TD_Long_Terms_First_Parser::compare_terms method
* WP 4.9.5 support

= 3.2.7 =

* WP 4.9.1 support
* The plugin classes renamed for compatibility with third-party themes

= 3.2.6 =

* Bug fix: in add_skip_tags method

= 3.2.5 =

* WP 4.7.4 support
* New feature: "Skip tags" options added

= 3.2.4 =

* WP 4.5.1 support
* Pagination update

= 3.2.3 =

* Bug fix: in setting disable_terms_descriptions option

= 3.2.2 =

* Bug fix: in setting cur_url variable

= 3.2.1 =

* Bug fix: in compare terms links with page link function

= 3.2 =

* WP 4.2 support
* Bug fix: form revert to add new term state after term update

= 3.1.9 =

* New feature: "Convert in custom posts types" options added
* New feature: shortcodes support added
* New feature: wptexturize support added
* New feature: word forms support added to CSV export
* Bug fix: edit term with post_id link type bug fixed

= 3.1.8 =

* Bug fix: empty nofollow and nofollow attributes notice fixed

= 3.1.7 =

* Bug fix: mistakes in links counting method fixed
* Mockpress updated

= 3.1.6 =

* Serbo-Croatian translations were added (thanks to Borisa Djuraskovic, http://www.webhostinghub.com)

= 3.1.5 =

* Interface updated in accordance to WP 3.8 requirements
* New feature: batch terms removal added
* New feature: nofollow attribute support added
* New feature: noindex tag support added

= 3.1.4 =

* Bug fix: Undefined index notice for text_before and text_after variables fixed
* Description updated

= 3.1.3 =

* "convert unlimited terms" with "consider existing links" option bug fixed
* Mockpress added to the plugin

= 3.1.2 =

* New feature: consider existing links option added
* Bug fix: parse single quotes in terms bug fixed

= 3.1.1 =

* Bug fix: empty word forms check is added

= 3.1.0 =

* New feature: CVS export is added
* New feature: Long terms first parser is added

= 3.0.5 =

* Additional checks added for external links to prevent self-linking
* Quotation marks are not included in the links when using the Simple quotes parser

= 3.0.4 =

* New feature: additional quotes type added to "Simple parser with quotes support"

= 3.0.3 =

* New feature: two additional quotes types added to "Simple parser with quotes support"
* Bug fix: number of post titles in autocomplete list increased

= 3.0.2 =

* New feature: terms search
* New option: open link in a new tab
* Bug fix: replacements in h{1..6} tags are fixed

= 3.0.1 =

* New option: wrap links with arbitrary text
* Bug fix: the plugin now uses default database charset and collation during table creation
* The plugin version updated according with very old releases

= 1.2.2 =

* New feature: disable plugin for individual post
* New option: maximum transformations number
* New option: add title attribute to links
* Bug fix: "Link to itself" in posts with non-latin URLs

= 1.2.1 =

* "Link to itself" bug fix

= 1.2.0 =

* Administration interface updated
* Database structure updated
* Export / import functions are added
* Packet terms upload function is added
* Different parsers support is added
* Simple parser is added
* Simple parser with quotes support is added
* Permalinks update function is added
* Bug fixes

= 1.1.8 =

* "Convert terms only on single pages" option is added.
* Custom posts types partial support is added.
* Bug fix: Convert first "-1" term occurrences means "no limit".

= 1.1.7 =

* Generation of terms links is optimized

= 1.1.6 =

* &laquo; &raquo; quotes support is added

= 1.1.5 =

* Now you can use quotes in terms

= 1.1.4 =

* Custom permalinks structure support added. Now when you change permalinks structure all links will be updated automatically.

= 1.1.3 =

* Bug fix: the plugins JS code was loaded on every admin page

= 1.1.2 =

* Bug fix: post titles with quotes was not properly escaped

= 1.1.1 =

* Term input field replaced with textarea (entering word forms will be more comfortable)
* Bug fixes

= 1.1 =

* Links Class attribute support added.
* Mistakes in Russian translated corrected

= 1.0 =

* Automated terms-to-links convertion in posts and/or coments.
* Limitation of terms-to-links convertion for one post.
* Support of several word forms.
* Terms CRUD operations.
