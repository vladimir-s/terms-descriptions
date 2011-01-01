=== Terms descriptions ===
Contributors: vladimir.s
Tags: post, page, links, plugin
Requires at least: 2.9
Tested up to: 3.0.1
Stable tag: trunk

This plugin allows you to create list of terms and assign links to them. Plugin replaces terms occurrences in your posts with appropriate links.

== Description ==

The main purpose of this plugin is easy link building.

For example, you can create a page or post with detail description of some term. Most likely, this term is used in other posts and it would be appropriate to put a links from that posts to page with description. But doing this operation manually is very time consuming task.

This WordPress plugin allow you to maintain a list of terms and assign links to them. Plugin automatically replaces terms occurrences in posts with appropriate links. You can control the number of terms replacements.

This plugin allows.

* Create, update and delete terms through admin interface (Tools -> Terms Descriptions). Each term can contain one or several words.
* Assign a link to a term. This link can point to your blog post/page or to a page on another site.
* Control how many terms will be converted to links in each post.
* Select where you want to replace terms, in posts content, in posts comments or both.
* Set several word forms for each term. For example, "apple|apples". Word forms should be separated with a "|" symbol.
* Search for terms in posts content and convert them to appropriate links. This task is performed automatically when plugin is activated.

Please, note that terms *will not* be replaced with links in following cases.

* If the term is already inside the link. E.g. if the link was created manually. So the plugin will not change any links that already exists in the post.
* If the term is located inside html tag. For example, inside `alt` attribute of `img` tag.
* If the term is located in `h1`-`h6` tags.
* If the term is part of another word. E.g. term = `car` and the word is `carrot`.
* If the term link is pointing to the current page/post.

**Important!**

Huge terms lists with hundreds of terms can increase page creation time. In such cases, consider to use caching plugin.

Description of this plugin is available in [Russian]( http://www.simplecoding.org/plagin-wordpress-terms-descriptions "Terms Descriptions WordPress Plugin"). And some useful information you can read from the [plugin blog](http://terms-descriptions.tumblr.com/) (in English).

== Installation ==

1. Download the zip file.
2. Extract `terms-descriptions` folder.
3. Upload `terms-descriptions` folder to your` wp-content/plugins` directory.
4. Log in to your WordPress blog.
5. Click on "Plugins".
6. Locate the "Terms Descriptions" plugin and click "Activate".
7. Go to Tools -> Terms Descriptions to create your list of terms.

== Screenshots ==

1. Admin page
2. Term creation form

== Changelog ==

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
