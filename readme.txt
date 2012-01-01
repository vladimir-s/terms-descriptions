=== Terms descriptions ===
Contributors: vladimir.s
Tags: post, page, links, plugin
Requires at least: 3.0
Tested up to: 3.3
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

Description of this plugin is available in [Russian]( http://www.simplecoding.org/plagin-wordpress-terms-descriptions "Terms Descriptions WordPress Plugin").

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

== Changelog ==

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
