<?php
/*
Plugin Name: Terms Descriptions
Plugin URI: http://www.simplecoding.org/plagin-wordpress-terms-descriptions
Description: This plugin allows you to create list of terms and assign links to them. Plugin automatically replaces terms occurrences in your posts with appropriate links. You can control the number of replacements. After activation you can create terms list on plugin administration page (Tools -> Terms Descriptions).
Version: 1.1.1
Author: Vladimir Statsenko
Author URI: http://www.simplecoding.org
License: GPLv3
*/

/*
== Description ==

The main purpose of this plugin is easy link building.

For example, you can create a page or post with detail description of some term. Most likely, this term is used in other posts and it would be appropriate to put a links from that posts to you page. But doing this operation manually is very time consuming task.

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
7. Go to "Tools" > " Terms Descriptions " to create your list of terms
*/

class Terms_descriptions {
	/**
	 * This method handles the requests for plugin admin page
	 * (create, edit, delete term, etc.)
	 */
	public function management_handler() {
		//load .mo file
		//its name must be terms-descriptions-locale.mo
		$plugin_dir = basename( dirname( __FILE__ ) );
		$domain = Terms_descriptions::get_text_domain();
		load_plugin_textdomain( $domain, '/wp-content/plugins/'.$plugin_dir, $plugin_dir );

		//include JS script, which replaces posts/pages lists
		$script_src = get_bloginfo( 'siteurl' ).'/wp-content/plugins/terms-descriptions/pagesposts.js';
		wp_enqueue_script( 'td_pagesposts', $script_src, array( 'jquery' ), false, true );

		$parent_file = 'tools.php?page=' . Terms_descriptions::get_plugin_slug();
		
		//if request come from plugin page
		if ( 'terms-descriptions.php' == substr( $parent_file, -22 ) ) {
			//reading terms list
			$terms = get_option( 'td_terms' );
			
			$action = $_GET['action'];
			
			switch ( $action ) {
				//adding term
				case 'add':
					check_admin_referer( 'td-add' );
					if ( !isset( $_GET['term'] ) || ( $term = self::remove_new_lines( $_GET['term'] ) ) == '' || !isset( $_GET['termpageid'] ) ) {
						$message = 4;
						wp_redirect( "$parent_file&m=$message" );
						break;
					}
					$page_id = $_GET['termpageid'];
					//saving term
					$key = Terms_descriptions::getNewKey( $terms );
					//if user set external link
					if ( 'http:' === substr( $page_id, 0, 5 ) ) {
						$terms[$key] = array(
								'term'=>$term,
								'pageid'=>0,
								'url'=>$page_id,
								'title'=>$page_id,
							);
					}
					//if user set internal link
					else {
						$page = get_page( $page_id );
						$terms[$key] = array(
								'term'=>$term,
								'pageid'=>$page_id,
								'url'=>get_permalink( $page->ID ),
								'title'=>$page->post_title,
							);
					}
					update_option( 'td_terms', $terms );
					$message = 1;
					wp_redirect( "$parent_file&m=$message" );
					break;
				//deleting term
				case 'delete':
					check_admin_referer( 'delete_term' );
					if ( isset( $_GET['termid'] ) && is_numeric( $_GET['termid'] )
							&& isset( $terms[$_GET['termid']] )) {
						unset( $terms[$_GET['termid']] );
						update_option( 'td_terms', $terms );
						$message = 2;
					}
					else {
						$message = 5;
					}
					wp_redirect( "$parent_file&m=$message" );
					break;
				//updating term
				case 'edit':
					check_admin_referer( 'td-edit' );
					if ( !isset( $_GET['term'] ) || ( $term = self::remove_new_lines( $_GET['term'] ) ) == '' || !isset( $_GET['termpageid'] ) ) {
						$message = 4;
						wp_redirect( "$parent_file&m=$message" );
						break;
					}
					if ( isset( $_GET['termid'] ) && is_numeric( $_GET['termid'] )
							&& isset( $terms[$_GET['termid']] )) {
						$page_id = $_GET['termpageid'];
						//if user set external link
						if ( 'http:' === substr( $page_id, 0, 5 ) ) {
							$terms[$_GET['termid']] = array(
								'term'=>$term,
								'pageid'=>0,
								'url'=>$page_id,
								'title'=>$page_id,
							);
						}
						//if user set internal link
						else {
							$page = get_page( $page_id );
							$terms[$_GET['termid']] = array(
								'term'=>$term,
								'pageid'=>( int )$page_id,
								'url'=>get_permalink( $page->ID ),
								'title'=>$page->post_title,
							);
						}
						update_option( 'td_terms', $terms );
						$message = 3;
					}
					else {
						$message = 6;
					}
					wp_redirect( "$parent_file&m=$message" );
					break;
			}
		}
	}
	
	/**
	 * This method returns plugins $menu_slug ( which is set in add_management_page function )
	 * ( terms_descriptions/terms-descriptions.php )
	 *
	 * @return string plugin slug
	 */
	public function get_plugin_slug() {
		return $_GET['page'];
	}
	
	/**
	 * This method returns unique text domain for localization
	 * 
	 * @return string localization domain
	 */
	public function get_text_domain() {
		return 'terms-descriptions';
	}
	
	/**
	 * Creates id for a new term
	 *
	 * @param array $terms array of existing terms
	 * @return int id for a new term
	 */
	public function getNewKey( $terms ) {
		//finding max existing id in array and return id+1,
		//if array is empty - return 1
		if ( false == $terms ) {
			return 1;
		}
		else {
			krsort( $terms, SORT_NUMERIC );
			return ( int )key( $terms ) + 1;
		}
	}
	
	/**
	 * This method adds the plugin administration page
	 */
	public function addPages() {
		$domain = Terms_descriptions::get_text_domain();
		
		add_management_page( __( 'Terms Descriptions', $domain ), __( 'Terms Descriptions', $domain ), "manage_options", __FILE__, array( 'Terms_descriptions', 'manage_terms' ));
	}
	
	/**
	 * This method creates html markup of the plugin administration page
	 */
	public function manage_terms() {
		$domain = Terms_descriptions::get_text_domain();
		//all commands handlers redirect to this page
		//they send messages in GET parameters
		if ( isset( $_GET['updated'] )) {
			echo '<div id="message" class="updated fade"><p>'.__( 'Options saved', $domain ).'</p></div>';
		}
		else if ( isset( $_GET['m'] ) && is_numeric( $_GET['m'] )) {
			$message = $_GET['m'];
			$messages[1] = __( 'Term created', $domain );
			$messages[2] = __( 'Term deleted', $domain );
			$messages[3] = __( 'Term updated', $domain );
			$messages[4] = __( 'Term create error', $domain );
			$messages[5] = __( 'Term delete error', $domain );
			$messages[6] = __( 'Term update error', $domain );
			echo '<div id="message" class="updated fade"><p>'.$messages[$message].'</p></div>';
		}
		
		$terms = get_option( 'td_terms' );
?>
<div class="wrap">
	<div id="icon-edit" class="icon32"><br/></div>
	<h2><?php _e( 'Terms descriptions plugin', $domain ); ?></h2>
	<br class="clear" />
	 <div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
			<?php Terms_descriptions::show_terms( $terms ); ?>
			</div>
		</div>
		<div id="col-left">
			<div class="col-wrap">
				<?php
				//showing term creation form
				if ( 'term_edit' == $_GET['action'] && !empty( $_GET['termid'] )
						&& is_numeric( $_GET['termid'] )) {
					Terms_descriptions::show_term_form( $_GET['termid'], $terms[$_GET['termid']] );
				}
				else {
					Terms_descriptions::show_term_form();
				}
				?>
			</div>
		</div>
	 </div>
</div>
<?php
	}
	
	/**
	 * This method creates form for editing or adding term.
	 * And an options form. 
	 *
	 * @param string $term_id selected term id
	 * @param array $term term data
	 */
	public function show_term_form( $term_id = '', $term = null ) {
		$domain = Terms_descriptions::get_text_domain();
		
		//если нужно показать форму изменения термина
		if ( !empty( $term_id ) && isset( $term )) {
			$form_title = __( 'Update term', $domain );
			$submit_text = __( 'Update', $domain );
			$form_open_tag = '<form name="editterm" id="editterm" method="get" action="" class="validate">';
			$action = 'edit';
			$nonse = 'td-edit';
			$term_id_field = '<input type="hidden" id="termid" name="termid" value="'.$term_id.'" />';
			//проверяем указывает ли ссылка на внешнюю страницу или на внутреннюю
			if ( 0 === ( int )$term['pageid'] ) {
				$page_type = 'external';
				$page_url = $term['url'];
			}
			else {
				$page_type = get_post_type( ( int )$term['pageid'] );
				$page_url = 'http://';
			}
		}
		else {
			$form_title = __( 'Create term', $domain );
			$submit_text = __( 'Create', $domain );
			$form_open_tag = '<form name="addterm" id="addterm" method="get" action="" class="validate">';
			$action = 'add';
			$nonse = 'td-add';
			$term_id_field = '';
			$page_url = 'http://';
		}
?>
<div class="form-wrap">
	<h3><?php echo $form_title; ?></h3>
	<div id="ajax-response"></div>
	<?php echo $form_open_tag; ?>
		<input type="hidden" name="page" value="<?php echo Terms_descriptions::get_plugin_slug() ?>"/>
		<input type="hidden" name="action" value="<?php echo $action ?>" />
		<?php echo $term_id_field; ?>
		<?php wp_original_referer_field( true, 'previous' ); wp_nonce_field( $nonse ); ?>
		<div class="form-field form-required">
			<label for="term"><?php _e( 'Term', $domain ); ?></label>
			<textarea name="term" id="term" cols="20" rows="5" ><?php echo ( isset( $term )) ? $term['term'] : ''; ?></textarea>
			<p><?php _e( 'Term can contain one or several words and will be converted to a link. If you want to use several word forms of a term, separate them with a "|". Example, "apple|apples".', $domain ); ?></p>
		</div>
		<div class="form-field form-required">
			<label for="termtarget"><?php _e( 'Link to', $domain ); ?></label>
			<select name="termtarget" id="termtarget">
				<?php $selected = ( 'page' == $page_type ) ? 'selected="selected"' : ''; ?>
				<option value="pages" <?php echo $selected; ?>><?php _e( 'Page', $domain ) ?></option>
				<?php $selected = ( 'post' == $page_type ) ? 'selected="selected"' : ''; ?>
				<option value="posts" <?php echo $selected; ?>><?php _e( 'Post', $domain ) ?></option>
				<?php $selected = ( 'external' == $page_type ) ? 'selected="selected"' : ''; ?>
				<option value="external" <?php echo $selected; ?>><?php _e( 'External link', $domain ) ?></option>
			</select>
		</div>
		<div class="form-field form-required">
			<label for="termpageid"><?php _e( 'Select page/post or enter external link', $domain ); ?></label>
			<?php if ( 'external' !== $page_type ) { ?>
				<select name="termpageid" id="termpageid">
					<option>---</option>
				</select>
			<?php } else { ?>
				<input type="text" name="termpageid" id="termpageid" value="<?php echo $page_url; ?>" />
			<?php } ?>
			<p><?php _e( 'Select a page or post you want to link to', $domain ); ?>.</p>
		</div>
		<p class="submit">
			<?php if ( 'edit' == $action ) echo '<a accesskey="c" title="'.__( 'Cancel', $domain ).'" class="cancel button-secondary alignright" href="tools.php?page=' . Terms_descriptions::get_plugin_slug() . '">'.__( 'Cancel', $domain ).'</a>' ?>
			<input type="submit" class="button-primary alignleft" name="submit" value="<?php echo $submit_text; ?>" />
		</p>
	</form>
</div>
<div class="form-wrap">
	<h3><?php _e( 'Options', $domain ); ?></h3>
	<form action="options.php" method="post" name="showon" id="showon">
		<?php settings_fields( 'td-settings-group' ); ?>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Convert terms to links', $domain ); ?></th>
				<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Convert terms to links', $domain ); ?></span></legend>
					<?php
						$show_on = get_option( 'td_target' );
						$t_posts = $t_comments = '';
						if ( is_array( $show_on )) {
							$t_posts = ( in_array( 'posts', $show_on ) ) ? 'checked="checked"' : '';
							$t_comments = ( in_array( 'comments', $show_on ) ) ? 'checked="checked"' : '';
						}
					?>
					<label><input type="checkbox" value="posts" name="td_target[]" <?php echo $t_posts; ?> /> <?php _e( 'in posts', $domain ); ?></label><br />
					<label><input type="checkbox" value="comments" name="td_target[]" <?php echo $t_comments; ?> /> <?php _e( 'in comments', $domain ); ?></label>
				</fieldset>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Convert first', $domain ); ?></span></legend>
					<?php $convert_count = get_option( 'td_count' ); ?>
					<label><?php _e( 'Convert first', $domain ); ?> <input type="text" value="<?php echo $convert_count; ?>" name="td_count" size="3" /> <?php _e( 'term occurrences.', $domain ); ?></label>
					<p class="hint"><?php _e( 'Enter a number. "-1" for converting all found terms. If a term has several word forms each of them will be counted.', $domain ); ?></p>
				</fieldset>
				</td>
			</tr>
			<tr>
				<td colspan="2">
				<fieldset>
					<legend class="screen-reader-text"><span><?php _e( 'Links Class attribute', $domain ); ?></span></legend>
					<?php $convert_count = get_option( 'td_class' ); ?>
					<label><?php _e( 'Links Class attribute', $domain ); ?> <input type="text" value="<?php echo get_option( 'td_class' ); ?>" name="td_class" size="15" /></label>
					<p class="hint"><?php _e( 'Leave this field empty if you didn\'t whant to add class attribute.', $domain ); ?></p>
				</fieldset>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary alignleft" name="submit" value="<?php _e( 'Save', $domain ); ?>" />
		</p>
	</form>
</div>
<?php
	}
	
	/**
	 * This method creates a list of terms
	 *
	 * @param array $terms terms data
	 */
	public function show_terms( $terms ) {
		$domain = Terms_descriptions::get_text_domain();
		
		if ( false == $terms ) {
			echo '<strong>'.__( 'Terms not found', $domain ).'</strong>';
		}
		else {
?>
<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><?php _e( 'Term', $domain ); ?></th>
		<th scope="col"><?php _e( 'Link', $domain ); ?></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="col"><?php _e( 'Term', $domain ); ?></th>
		<th scope="col"><?php _e( 'Link', $domain ); ?></th>
	</tr>
	</tfoot>
	<tbody id="the-list">
	<?php foreach ( $terms as $key => $term ) { ?>
		<tr class="iedit alternate">
			<td class="name column-name">
				<span style="display:block"><strong><?php echo $term['term']; ?></strong></span>
				<div class="row-actions">
					<span class="edit"><a href="tools.php?page=<?php echo Terms_descriptions::get_plugin_slug(); ?>&amp;action=term_edit&amp;termid=<?php echo $key; ?>"><?php _e( 'Edit', $domain ); ?></a></span> |
					<span class="delete">
						<?php
							$link = 'tools.php?page=' . Terms_descriptions::get_plugin_slug() . '&amp;action=delete&amp;termid=' . $key;
							$link = ( function_exists( 'wp_nonce_url' ) ) ? wp_nonce_url( $link, 'delete_term' ) : $link;
						?>
						<a class="submitdelete" href="<?php echo $link ?>" onclick="if ( confirm( '<?php _e( 'Are you sure?', $domain ); ?>' ) ) { return true;}return false;">
							<?php _e( 'Delete', $domain ); ?>
						</a>
					</span>
				</div>
			</td>
			<td class="name column-name">
				<a href="<?php echo $term['url']; ?>"><?php echo $term['title']; ?></a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php
		}
	}
	
	/**
	 * Register checkbox group for options form
	 */
	public function registerTdSettings() {
		register_setting( 'td-settings-group', 'td_target' );
		register_setting( 'td-settings-group', 'td_class' );
		//in the third parameter of register_setting we passing name of
		//the function that will be called befoure saving new value
		register_setting( 'td-settings-group', 'td_count', 'intval' );
	}
	
	/**
	 * This method converts terms to links in posts and/or comments
	 * 
	 * @param string $content the post content
	 * @return string the post content with terms converted to links
	 */
	public function termReplace( $content ) {
		//getting the post id
		$cur_id = get_the_id();
		//getting convertions limit
		$replace_count = get_option( 'td_count' );
		if ( false == $replace_count ) {
			$replace_count = -1; //no limit
		}
		$terms = get_option( 'td_terms' );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				//if the term link is pointing to the current page/post
				if ( $cur_id == $term['pageid'] ) {
					continue;
				}
				
				/*
				Term *will not* be replaced with link in following cases.
				
				1) If the term is already inside the link.
				2) If the term is located inside html tag. For example, inside `alt` attribute of `img` tag.
				3) If the term is located in `h1`-`h6` tags.
				4) If the term is part of another word. E.g. term = `car` and the word is `carrot`.
				5) If the term link is pointing to the current page/post
				*/
				
				$replace_terms = $replace_count;
				
				//regular expression for deviding post context
				//(devision is made by html tags)
				preg_match_all( '/<a\s.*?>.*?<\/a>|<h\d.*?\/h\d>|<.*?>/isu', $content,
					$matches, PREG_OFFSET_CAPTURE );
				$result = '';
				$start_pos = 0;
				
				//cheking if the term contains several word forms
				$term_search_str = self::parse_term( $term['term'] );
				
				$class_attr = get_option( 'td_class' );
				if ( $class_attr !== false && trim( $class_attr ) !== '' ) {
					$class_attr = ' class="'.$class_attr.'"';
				}
				
				//regular expression for term replacement
				$replace_re = '/([\s\r\n\:\;\!\?\.\,\)\(<>]{1}|^)('.$term_search_str.')([\s\r\n\:\;\!\?\.\,\)\(<>]{1}|$)/isu';

				foreach ( $matches[0] as $match ) {
					//is their a text befoure this occuarance?
					$length = $match[1] - $start_pos;
					if ( $length > 0 ) {
						//searching for a term
						$text = substr( $content, $start_pos, $length );
						if ( $replace_terms >= 0 ) {
							$result .= preg_replace( $replace_re, '$1<a href="'.$term['url'].'"'.$class_attr.'>$2</a>$3', $text, $replace_terms, $replaced );
							$replace_terms -= $replaced;
						}
						else {
							$result .= preg_replace( $replace_re, '$1<a href="'.$term['url'].'"'.$class_attr.'>$2</a>$3', $text );
						}
						
					}
					//adding html tag to the result
					$result .= $match[0];
					$start_pos = $match[1] + strlen( $match[0] );
				}
				//cheking if all post content was parsed
				//(problem may occur if the closing tag in post content was missed)
				if ( $start_pos < strlen( $content )) {
					$text = substr( $content, $start_pos );
					if ( $replace_terms >= 0 ) {
						$result .= preg_replace( $replace_re, '$1<a href="'.$term['url'].'"'.$class_attr.'>$2</a>$3', $text, $replace_terms, $replaced );
						$replace_terms -= $replaced;
					}
					else {
						$result .= preg_replace( $replace_re, '$1<a href="'.$term['url'].'"'.$class_attr.'>$2</a>$3', $text );
					}
				}
				$content = $result;
			}
		}
		return $content;
	}
	
	/**
	 * This method creates JavaScript code with blog posts and pages lists.
	 */
	function generate_js() {
		$term_id = ( isset( $_GET['termid'] ) ) ? $_GET['termid'] : null;
		$page_url = 'http://';
		if ( $term_id !== null ) {
			$terms = get_option( 'td_terms' );
			$page_id = $terms[$term_id]['pageid'];
			if ( 0 == $page_id ) {
				$page_url = $terms[$term_id]['url'];
			}
		}
		echo '<script type="text/javascript"> /* <![CDATA[ */'."\n";
		
		//creating posts list
		$posts = get_posts( array( 'numberposts' => -1 ) );
		if ( count( $posts ) > 0 ) {
			echo 'var posts = "";'."\n";
			foreach ( $posts as $i => $post ) {
				if ( isset( $page_id ) && $post->ID == $page_id ) {
					$selected = 'selected=\"selected\"';
				}
				else {
					$selected = '';
				}
				echo 'posts += "<option value=\"'.$post->ID.'\" '.$selected.'>'.$post->post_title.'</option>";'."\n";
			}
		}
		
		//creating pages list
		$pages = get_pages();
		if ( count( $pages ) > 0 ) {
			//создаём элементы списка тегов option с перечнем страниц
			echo 'var pages = "";'."\n";
			foreach ( $pages as $i => $page ) {
				if ( isset( $page_id ) && $page->ID == $page_id ) {
					$selected = 'selected=\"selected\"';
				}
				else {
					$selected = '';
				}
				echo 'pages += "<option value=\"'.$page->ID.'\" '.$selected.'>'.$page->post_title.'</option>";'."\n";
			}
		}
		
		//saving external page link
		echo 'var external_page = "'.$page_url.'";'."\n";
		
		echo '/* ]]> */</script>';
	}
	
	function remove_new_lines( $str ) {
		$res = str_replace( "\r", "", $str );
		return trim( str_replace( "\n", "", $res ) );
	}
	
	/**
	 * This method parse term and trying to correct mistakes in word forms settins (if any)
	 * 
	 * @param string $term term (possibly with several word forms)
	 * @return string term in form that can be used in regular expression
	 */
	function parse_term( $term ) {
		$term_forms = explode( '|', $term );
		$term_search_str = '';
		if ( empty( $term_forms ) ) {
			$term_search_str = $term;
		}
		else {
			//deleting empty elements
			foreach ( $term_forms as $i => $form ) {
				if ( trim( $form ) === '' ) {
					unset( $term_forms[$i] );
				}
				else {
					$term_forms[$i] = trim( $term_forms[$i] );
				}
			}
			$term_search_str = implode( '|', $term_forms );
		}
		return $term_search_str;
	}
}

//request handler
add_action( 'admin_init', array( 'Terms_descriptions', 'management_handler' ) );
//options form handler
add_action( 'admin_init', array( 'Terms_descriptions', 'registerTdSettings' ) );

//filters for posts and/or comments
$tdTarget = get_option( 'td_target' );
if ( is_array( $tdTarget ) && in_array( 'posts', $tdTarget )) {
	add_filter( 'the_content', array( 'Terms_descriptions', 'termReplace' ) );
}
if ( is_array( $tdTarget ) && in_array( 'comments', $tdTarget )) {
	add_filter( 'comment_text', array( 'Terms_descriptions', 'termReplace' ) );
}
  
//creating plugin administration page
add_action( 'admin_menu', array( 'Terms_descriptions', 'addPages' ) );

//adding JS code to admin page
add_action( 'admin_footer', array( 'Terms_descriptions', 'generate_js' ) );

//end of terms-descriptions.php