<?php
/**
 * This class creates Options page in Terms menu
 */
class SCO_TD_Admin_Options {
    private $page = '';
    
    /**
     * Constuctor. Sets the actions handlers.
     */
    public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
    }
    
    /**
     * Creates option page.
     */
    public function admin_menu() {
        add_submenu_page( 'terms-descriptions', __( 'Options', 'terms-descriptions')
                , __( 'Options', 'terms-descriptions'), 'manage_options', 'td-options'
                , array( $this, 'options_page' ));
    }
    
    /**
     * Register plugin options
     */
    public function admin_init() {
        register_setting( 'td_settings_options', 'td_options', array( $this, 'validate_options' ) );
    }
    
    /**
     * Options page HTML
     */
    public function options_page() {
        //reading current options values
        $options = get_option('td_options');
        //if there is no options using default values
        if ( false === $options ) {
            $terms_class = new SCO_TD_Admin_Terms();
            $options = $terms_class->get_default_options();
            add_option( 'td_options', $options );
        }
?>
<div class="wrap">
	<h2><?php _e( 'Options', 'terms-descriptions'); ?></h2>
    <?php if ( isset( $_GET[ 'settings-updated' ] ) && 'true' === $_GET[ 'settings-updated' ] ) { ?>
        <div id="setting-error-settings_updated" class="updated settings-error"> 
            <p><strong><?php _e( 'Options saved', 'terms-descriptions' ); ?></strong></p>
        </div>
    <?php } ?>
    <form method="post" action="options.php">
        <?php settings_fields('td_settings_options'); ?>
        <table class="form-table">
            <tbody>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Convert terms', 'terms-descriptions' ); ?></th>
                    <td>
	                    <?php
	                    $post_types = get_post_types( array( 'public' => true ), 'objects' );
	                    unset( $post_types[ 'attachment' ] );
	                    foreach ( $post_types as $type => $data ) {
	                    ?>
		                    <label><input name="td_options[convert_in__<?php echo $type; ?>]" type="checkbox"
		                                  id="convert_in__<?php echo $type; ?>"
				                    <?php if (isset($options[ 'convert_in__'.$type ])) { checked( $options[ 'convert_in__'.$type ], 'on' ); } ?> />
			                        <?php _e( 'in posts of type', 'terms-descriptions' ); ?> "<?php echo $data->labels->name; ?>"
		                    </label><br />
	                    <?php
	                    }
	                    ?>

                        <label><input name="td_options[convert_in_comments]" type="checkbox" id="convert_in_comments"
		                        <?php checked( $options[ 'convert_in_comments' ], 'on' ); ?> /> <?php _e( 'in comments', 'terms-descriptions' ); ?></label>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Convert terms in shortcodes', 'terms-descriptions' ); ?></th>
                    <td>
                        <label><input name="td_options[convert_in_shortcodes]" type="checkbox" id="convert_in_posts"
		                        <?php if(isset($options[ 'convert_in_shortcodes' ])) { checked( $options[ 'convert_in_shortcodes' ], 'on' ); } ?> /></label><br />
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Convert the first', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[convert_first_n_terms]" type="text" id="convert_first_n_terms" value="<?php echo $options[ 'convert_first_n_terms' ]; ?>" class="small-text" /> <?php _e( 'occurrences of each term.', 'terms-descriptions' ); ?>
                        <span class="description"><?php _e( 'Set "-1" if you want to convert all terms.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Maximum transformations number', 'terms-descriptions' ); ?></th>
                    <td>
                        <?php
                        if ( !isset( $options[ 'convert_total' ] ) || $options[ 'convert_total' ] === null ) {
                            $options[ 'convert_total' ] = '-1';
                        }
                        ?>
                        <input name="td_options[convert_total]" type="text" id="convert_total" value="<?php echo $options[ 'convert_total' ]; ?>" class="small-text" />
                        <span class="description"><?php _e( 'Set "-1" if you don\'t want to use this limitation.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Consider existing links', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[consider_existing_links]" type="checkbox" id="consider_existing_links" <?php if ( isset( $options[ 'consider_existing_links' ] ) ) { checked( $options[ 'consider_existing_links' ], 'on' ); } ?> />
                        <span class="description"><?php _e( 'If this option is checked plugin will count links that are added by hand.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Add CSS class', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[class]" type="text" id="class" value="<?php echo $options[ 'class' ]; ?>" /> <?php _e( 'to terms links.', 'terms-descriptions' ); ?>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Add title attribute to links', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[show_title]" type="checkbox" id="show_title" <?php if ( isset( $options[ 'show_title' ] ) ) { checked( $options[ 'show_title' ], 'on' ); } ?> />
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Wrap link', 'terms-descriptions' ); ?></th>
                    <td>
                        <?php _e( 'text before', 'terms-descriptions' ); ?> <input name="td_options[text_before]" type="text" id="text_before" value="<?php if ( isset( $options[ 'text_before' ] ) ) { echo $options[ 'text_before' ]; } ?>" />
                        <span class="description"><?php _e( 'example: &lt;strong&gt;', 'terms-descriptions' ); ?></span><br />
                        <?php _e( 'text after', 'terms-descriptions' ); ?> <input name="td_options[text_after]" type="text" id="text_after" value="<?php if ( isset( $options[ 'text_after' ] ) ) { echo $options[ 'text_after' ]; } ?>" />
                        <span class="description"><?php _e( 'example: &lt;/strong&gt;', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Convert terms only on single pages', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[convert_only_single]" type="checkbox" id="convert_only_single" <?php if ( isset( $options[ 'convert_only_single' ] ) ) { checked( $options[ 'convert_only_single' ], 'on' ); } ?> />
                        <span class="description"><?php _e( 'Terms will not be converted on home, categories and archives pages.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Open link in a new tab', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[open_new_tab]" type="checkbox" id="open_new_tab" <?php if ( isset( $options[ 'open_new_tab' ] ) ) { checked( $options[ 'open_new_tab' ], 'on' ); } ?> />
                        <span class="description"><?php _e( 'If this option is checked plugin will add target="_blank" to links.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Add nofollow to external links', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[add_nofollow]" type="checkbox" id="add_nofollow" <?php if ( isset( $options[ 'add_nofollow' ] ) ) { checked( $options[ 'add_nofollow' ], 'on' ); } ?> />
                        <span class="description"><?php _e( 'If this option is checked plugin will add rel="nofollow" to <strong>external</strong> links.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Wrap external links with noindex tag', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[add_noindex]" type="checkbox" id="add_noindex" <?php if ( isset( $options[ 'add_noindex' ] ) ) { checked( $options[ 'add_noindex' ], 'on' ); } ?> />
                        <span class="description"><?php _e( 'If this option is checked plugin will wrap <strong>external</strong> links with &lt;noindex&gt; tag.', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Skip tags', 'terms-descriptions' ); ?></th>
                    <td>
                        <input name="td_options[skip_tags]" type="text" id="skip_tags" class="large-text code" value="<?php echo ( isset( $options[ 'skip_tags' ] ) ) ? $options[ 'skip_tags' ] : ''; ?>" />
                        <p class="description"><?php _e( 'The plugin skips text inside tags like <code>a</code>, <code>h1..6</code>, <code>canvas</code>, <code>code</code>, etc. This option allows you to add additional tags to skip. Use regular expressions to specify tags and <code>|</code> (vertical line) to separate them.', 'terms-descriptions' ); ?></p>
                        <p class="description"><code>&lt;em.*?&lt;\/em&gt;|&lt;span.*?&lt;\/span&gt;</code></p>
                        <p class="description"><?php _e( '<strong>Use this option with care</strong>. In most cases the default set of tags will be enough.', 'terms-descriptions' ) ?></p>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Show', 'terms-descriptions' ); ?></th>
                    <td>
                        <select name="td_options[terms_per_page]" id="terms_per_page">
                            <option value="10" <?php selected( $options[ 'terms_per_page' ], 10); ?>>10</option>
                            <option value="20" <?php selected( $options[ 'terms_per_page' ], 20); ?>>20</option>
                            <option value="50" <?php selected( $options[ 'terms_per_page' ], 50); ?>>50</option>
                            <option value="100" <?php selected( $options[ 'terms_per_page' ], 100); ?>>100</option>
                            <option value="200" <?php selected( $options[ 'terms_per_page' ], 200); ?>>200</option>
                            <option value="500" <?php selected( $options[ 'terms_per_page' ], 500); ?>>500</option>
                        </select>
                        <span class="description"><?php _e( 'terms on a page (in admin area)', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
                <tr valign="middle">
                    <th scope="row"><?php _e( 'Parser', 'terms-descriptions' ); ?></th>
                    <td>
                        <label><input type="radio" name="td_options[parser]" id="simple_parser" value="simple_parser"
                            <?php checked( $options[ 'parser' ], 'simple_parser' ); ?> />
                            <?php _e( 'Simple parser', 'terms-descriptions' ); ?></label><br />
                        <label><input type="radio" name="td_options[parser]" id="quotes_parser" value="quotes_parser"
                            <?php checked( $options[ 'parser' ], 'quotes_parser' ); ?> />
                            <?php _e( 'Simple parser with quotes support', 'terms-descriptions' ); ?></label><br />
                        <label><input type="radio" name="td_options[parser]" id="long_terms_first_parser" value="long_terms_first_parser"
                            <?php checked( $options[ 'parser' ], 'long_terms_first_parser' ); ?> />
                            <?php _e( 'Long terms first parser', 'terms-descriptions' ); ?></label>
                            <span class="description"><?php _e( 'Orders terms by their lengths before searching them in text', 'terms-descriptions' ); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e( 'Update', 'terms-descriptions' ); ?>">
        </p>
    </form>
</div>
<?php        
    }
    
    /**
     * Options validation
     *
     * @param array $input new options values
     * @return array validated options
     */
    public function validate_options( $input ) {
        //reading current values
        $old_options = get_option('td_options');
        //checking new values
        if ( (int)$input[ 'convert_first_n_terms' ] <= 0 ) {
            $input[ 'convert_first_n_terms' ] = '-1';
        }
        else {
            $input[ 'convert_first_n_terms' ] = (int)$input[ 'convert_first_n_terms' ];
        }
        if ( !isset( $input[ 'terms_per_page' ] ) || ( int )$input[ 'terms_per_page' ] <= 0 ) {
            $input[ 'terms_per_page' ] = 20;
        }
        if ( !isset( $input[ 'convert_in_posts' ] ) ) {
            $input[ 'convert_in_posts' ] = false;
        }
	    $post_types = get_post_types( array( 'public' => true ), 'names' );
	    unset( $post_types[ 'attachment' ] );
	    foreach ( $post_types as $type ) {
		    if ( !isset( $input[ 'convert_in__'.$type ] ) ) {
			    $input[ 'convert_in__'.$type ] = false;
		    }
	    }
        if ( !isset( $input[ 'convert_in_comments' ] ) ) {
            $input[ 'convert_in_comments' ] = false;
        }
        if ( !isset( $input[ 'convert_in_shortcodes' ] ) ) {
            $input[ 'convert_in_shortcodes' ] = false;
        }
        if ( !isset( $input[ 'consider_existing_links' ] ) ) {
            $input[ 'consider_existing_links' ] = false;
        }
        if ( !isset( $input[ 'convert_only_single' ] ) ) {
            $input[ 'convert_only_single' ] = false;
        }
        if ( !isset( $input[ 'open_new_tab' ] ) ) {
            $input[ 'open_new_tab' ] = false;
        }
        if ( !isset( $input[ 'add_nofollow' ] ) ) {
            $input[ 'add_nofollow' ] = false;
        }
        if ( !isset( $input[ 'add_noindex' ] ) ) {
            $input[ 'add_noindex' ] = false;
        }
        if ( !isset( $input[ 'parser' ] ) ) {
            $input[ 'parser' ] = 'simple_parser';
        }
        if ( !isset( $input[ 'convert_total' ] ) || ( int )$input[ 'convert_total' ] <= 0 ) {
            $input[ 'convert_total' ] = -1;
        }
        if ( !isset( $input[ 'show_title' ] ) ) {
            $input[ 'show_title' ] = false;
        }
        if ( !isset( $input[ 'show_before' ] ) ) {
            $input[ 'show_before' ] = '';
        }
        if ( !isset( $input[ 'show_after' ] ) ) {
            $input[ 'show_after' ] = '';
        }
        if ( !isset( $input[ 'skip_tags' ] ) ) {
            $input[ 'skip_tags' ] = '';
        }
        if ( false !== $old_options ) {
            return array_merge( $old_options, $input );
        }
        else {
            return $input;
        }
    }
}

$tdao = new SCO_TD_Admin_Options();