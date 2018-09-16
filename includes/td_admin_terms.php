<?php
/**
 * This class creates Terms page in Terms menu
 */
class SCO_TD_Admin_Terms {
    private $page = '';
    private $post_types = array();
    //options defaults
    private $td_options = array(
                'terms_per_page' => 20,
                'convert_in_posts' => 'on',
                'convert_in_comments' => false,
                'convert_first_n_terms' => 3,
                'class' => '',
                'convert_only_single' => true,
                'parser' => 'simple_parser',
                'text_before' => '',
                'text_after' => '',
                'convert_total' => -1,
                'consider_existing_links' => false,
                'open_new_tab' => false,
                'show_title' => false,
                'show_before' => '',
                'show_after' => '',
                'skip_tags' => '',
            );
    private $terms_ids = array();

    /**
     * Constuctor. Sets the actions handlers.
     */
    public function __construct() {
        register_activation_hook( TD_FILE, array( $this, 'install' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_notices', array( $this, 'update_message' ) );
        add_action( 'admin_init', array( $this, 'update_db' ) );
    }

    /**
     * Plugin installation.
     * 
     * @global type $wpdb wordpress database class
     */
    public function install() {
        global $wpdb;
        //creating database table for terms

        $charset_collate = '';
        if ( ! empty($wpdb->charset) ) {
            $charset_collate = " DEFAULT CHARACTER SET $wpdb->charset";
        }
        if ( ! empty($wpdb->collate) ) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
        
        $terms_table_name = $wpdb->prefix . 'td_terms';
        if( $wpdb->get_var( 'show tables like "' . $terms_table_name . '"' ) != $terms_table_name ) {
            //creating terms table
            $sql = "CREATE TABLE " . $terms_table_name . " (
                t_id bigint(20) NOT NULL AUTO_INCREMENT,
                t_post_id bigint(20) NOT NULL,
                t_post_title VARCHAR(255),
                t_post_url VARCHAR(255),
                t_post_type VARCHAR(255) NOT NULL,
                t_term TEXT NOT NULL,
                UNIQUE KEY t_id (t_id)
                )" . $charset_collate . ";";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        
        //if there is no plugin options data from new and previous versions
        if ( false === get_option( 'td_terms' ) && false === get_option( 'td_options' ) ) {
            //setting default options values
            add_option( 'td_options', $this->td_options );
        }
    }
    
    /**
     * Returns default options values 
     *
     * @return array default options values 
     */
    public function get_default_options() {
        return $this->td_options;
    }
    
    /**
     * This method shows warning message if previous plugin version is lower than 1.2.0
     */
    public function update_message() {
        if ( false !== ( $terms = get_option( 'td_terms' ) ) ) {
?>
<div id="message" class="updated">
    <p>
        <?php _e( 'Terms Descriptions plugin is almost updated. Please, BACKUP YOUR DATABASE and press following button to', 'terms-descriptions' ); ?>
        <a href="<?php echo wp_nonce_url( 'admin.php?page=' . 'terms-descriptions' . '&action=update_db', 'update_db' ); ?>" class="button-secondary"><?php _e( 'Update DB', 'terms-descriptions' ); ?></a>
    </p>
</div>
<?php
        }
    }
    
    /**
     * This method converts data to v.1.2.0 format.
     *
     * @global wpdb $wpdb wordpress database class
     */
    public function update_db() {
        if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'update_db'
                && isset( $_GET[ '_wpnonce' ] )
                && wp_verify_nonce( $_GET[ '_wpnonce' ], 'update_db' ) ) {
            //importing data from plugin previous versions
            if ( false !== ( $terms = get_option( 'td_terms' ) ) ) {
                global $wpdb;
                $terms_table_name = $wpdb->prefix . 'td_terms';
                
                //creating database
                $this->install();
                
                if ( is_array( $terms ) ) {
                    $insert_sql = 'INSERT INTO ' . $terms_table_name
                            . ' (t_post_id,t_post_title,t_post_url,t_post_type,t_term) VALUES ';
                    $terms_values = array();
                    foreach ( $terms as $term ) {
                        if ( $term[ 'pageid' ] === '0' ) {
                            $post_type = 'ext_link';
                        }
                        else {
                            $post_type = get_post_type( $term[ 'pageid' ] );
                        }
                        $terms_values[] = $wpdb->prepare(
                                '(%d,%s,%s,%s,%s)'
                                , array( $term[ 'pageid' ], $term[ 'title' ], $term[ 'url' ], $post_type, $term[ 'term' ] )
                        );
                    }
                    $wpdb->query( $insert_sql . implode(',', $terms_values) );
                    delete_option( 'td_terms' );
                }
            }
            
            //importing options from plugin previous versions
            if ( false === get_option( 'td_options' ) ) {
                if ( false !== get_option( 'td_class' ) ) {
                    $this->td_options[ 'class' ] = get_option( 'td_class' );
                }
                if ( false !== get_option( 'td_count' ) ) {
                    $this->td_options[ 'convert_first_n_terms' ] = get_option( 'td_count' );
                }
                if ( false !== get_option( 'td_convert_only_single' ) ) {
                    $this->td_options[ 'convert_only_single' ] = get_option( 'td_convert_only_single' );
                }
                if ( false !== ( $targets = get_option( 'td_target' ) ) ) {
                    if ( in_array( 'posts', $targets ) ) {
                        $this->td_options[ 'convert_in_posts' ] = true;
                    }
                    if ( in_array( 'comments', $targets ) ) {
                        $this->td_options[ 'convert_in_comments' ] = true;
                    }
                }
                
                add_option( 'td_options', $this->td_options );
                
                delete_option( 'td_target' );
                delete_option( 'td_class' );
                delete_option( 'td_count' );
                delete_option( 'td_convert_only_single' );
            }
            
            wp_redirect( trailingslashit( site_url() ) . 'wp-admin/admin.php?page=' . 'terms-descriptions' );
            die();
        }
    }
    
    /**
     * Creating admin menu
     *
     * @global wpdb $wpdb wordpress database class
     */
    public function admin_menu() {
        load_plugin_textdomain( 'terms-descriptions', false, 'terms-descriptions' . '/lang' );
        $this->page = add_menu_page(
            __( 'Terms', 'terms-descriptions' )
            , __( 'Terms', 'terms-descriptions' )
            , 'manage_options'
            , 'terms-descriptions'
            , array( $this, 'terms_page' )
            , ''
        );
        add_action( 'admin_print_scripts-' . $this->page, array( $this, 'load_scripts' ) );
        add_action( 'admin_print_styles-' . $this->page, array( $this, 'load_styles' ) );
        
        $this->post_types = get_post_types( array(
            'public' => true,
            'show_ui' => true,
        ), 'objects' );
        
        //getting all terms ids (used for permalinks updates)
        global $wpdb;
        $this->terms_ids = $wpdb->get_col('SELECT t_id FROM ' . $wpdb->prefix . 'td_terms', 0 );
    }

    /**
     * Loading JS files
     *
     * @global wpdb $wpdb wordpress database class
     */
    public function load_scripts() {
        wp_enqueue_script( 'td_terms', TD_URL . '/js/terms.js'
                , array( 'jquery-ui-autocomplete', 'jquery-ui-dialog', 'backbone' ), '1.0', true );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        //translations for use in JS code and array of terms ids
        wp_localize_script( 'td_terms', 'td_messages', array(
            'enter_term' => __( 'Enter the term, please', 'terms-descriptions' ),
            'enter_link' => __( 'Enter the link, please', 'terms-descriptions' ),
            'url_save' => get_bloginfo( 'wpurl' ) . '/wp-admin/admin-ajax.php',
            'edit' => __( 'Edit', 'terms-descriptions' ),
            'remove' => __( 'Delete', 'terms-descriptions' ),
            'confirm_delete' => __( 'Are you sure?', 'terms-descriptions' ),
            'add_term' => __( 'Add term', 'terms-descriptions' ),
            'edit_term' => __( 'Update term', 'terms-descriptions' ),
            'cancel_edit_term' => __( 'Cancel', 'terms-descriptions' ),
            'nonce' => wp_create_nonce( 'td_delete_term' ),
            'nonce_update_permalink' => wp_create_nonce( 'td_update_permalink' ),
            'term_add' => __( 'New term was added', 'terms-descriptions' ),
            'term_update' => __( 'The term was updated', 'terms-descriptions' ),
            'updating_permalinks' => __( 'Updating...', 'terms-descriptions' ),
            'done' => __( 'Done!', 'terms-descriptions' ),
            'select' => __( 'Select', 'terms-descriptions' ),
            'terms_ids' => json_encode( $this->terms_ids ),
            'dbl_click_to_open_list' => __('Title. Double click to open the titles list or type some letters', 'terms-descriptions'),
            'ext_link_title' => __('Title attribute text', 'terms-descriptions'),
            'post_id' => __('Post ID', 'terms-descriptions'),
        ) );
        
        global $wpdb;
        $types_names = array();
        foreach ( $this->post_types as $type_name => $type ) {
            $types_names[] = '"' . $type_name . '"';
        }
        //getting blog posts for use in JS code (autocomplete field)
        $posts = $wpdb->get_results( 'SELECT ID, post_title, post_type FROM '
                . $wpdb->posts . ' WHERE post_type IN (' . implode( ',', $types_names )
                . ') AND post_status IN ("draft", "publish")' );
        echo '<script type="text/javascript">' . "\n"
            . '//<![CDATA[' . "\n"
            . 'var td_posts=' . json_encode( $posts ) . "\n"
            . '//]]>' . "\n"
            . '</script>';
    }
    
    /**
     * Including CSS files
     */
    public function load_styles() {
        wp_enqueue_style( 'td_css', TD_URL . '/css/td_styles.css' );
    }
    
    /**
     * Terms page HTML
     *
     * @global type $wpdb wordpress database class
     */
    public function terms_page() {
?>
<div class="wrap">
    <h2><?php _e( 'Terms', 'terms-descriptions' ); ?></h2>
    <form action="#" method="post" id="td_add_term_form">
    <?php wp_nonce_field( 'td_add_term' ); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="td_term"><?php _e( 'Term', 'terms-descriptions' ); ?></label></th>
            <td>
                <textarea name="td_term" id="td_term" cols="50" rows="3" class="large-text code"></textarea>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="td_link"><?php _e( 'Link', 'terms-descriptions' ); ?></label></th>
            <td>
                <table class="form-table">
                    <tr>
                        <td style="width: 250px; vertical-align: top;">
                            <label for="td_content_type"><?php _e( 'Link to', 'terms-descriptions' ); ?></label>
                            <select name="td_content_type" id="td_content_type">
                                <?php
                                foreach ( $this->post_types as $type_name => $type ) {
                                    echo '<option value="'.$type_name.'">'.$type->labels->singular_name.'</option>';
                                }
                                ?>
                                <option value="ext_link"><?php _e( 'External link', 'terms-descriptions' ); ?></option>
                                <option value="post_id"><?php _e( 'Post ID', 'terms-descriptions' ); ?></option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="td_link" id="td_link" class="regular-text" />
                            <input type="text" name="td_title" id="td_title" class="regular-text hidden" disabled="disabled" />
                            <input type="hidden" name="td_post_id" id="td_post_id" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
        <p class="submit">
            <input type="submit" name="td_add_term" id="td_add_term" class="button-primary" value="<?php _e( 'Add term', 'terms-descriptions' ); ?>">
            <span class="spinner" id="save_term_spinner"></span>
        </p>
    </form>

    <hr class="form-divider" />
    <form action="#" method="post" id="td_update_permalinks">
        <span class="description"><?php _e('Press this button if you have updated permalinks structure.', 'terms-descriptions'); ?></span>
        <input type="submit" class="button-primary" name="td_update_permalinks_btn" value="<?php _e( 'Update permalinks', 'terms-descriptions' ); ?>" />
    </form>
    <hr class="form-divider" />

    <?php
    global $wpdb;
    //getting terms data
    $options = get_option( 'td_options' );
    
    $search_str = '';
    $where_clause = '';
    if ( isset( $_GET[ 'term_search' ] ) && '' !== trim( $_GET[ 'term_search' ] ) ) {
        $search_str = $_GET[ 'term_search' ];
        $where_clause = ' WHERE t_term LIKE "%' . $wpdb->esc_like( $search_str ) . '%" ';
    }
    
    $terms_count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'td_terms' . $where_clause );
    //preparing pagination
    $cur_page = 1;
    if ( isset( $_GET[ 'term_page' ] ) && ( int )$_GET[ 'term_page' ] > 0 ) {
        $cur_page = ( int )$_GET[ 'term_page' ];
    }
    
    $terms_per_page = $options[ 'terms_per_page' ];
    if ( false === $terms_per_page ) {
        $terms_per_page = 10;
    }
    ?>
    
    <div id="terms_filter" class="tablenav top">
        <form id="filter_form" class="alignleft" action="<?php echo get_admin_url( null, 'admin.php' ); ?>" method="get">
            <div class="alignleft actions td_remove_selected">
                <button class="button action" id="td_remove_selected_btn" disabled="disabled"><?php _e( 'Remove selected', 'terms-descriptions' ); ?></button>
            </div>
            <label><?php _e( 'Search', 'terms-descriptions' ); ?> <input type="text" name="term_search" value="<?php echo $search_str; ?>" /></label>
            <input type="submit" class="button action" value="<?php _e( 'Search', 'terms-descriptions' ); ?>" />
            <input type="hidden" name="page" value="terms-descriptions" />
        <?php
        if ( isset( $_GET[ 'term_search' ] ) ) {
            echo '<a href="' . get_admin_url( null, 'admin.php' ) . '?page=terms-descriptions" class="button" id="clear_filter_btn">' . __( 'Cancel', 'terms-descriptions' ) . '</a>';
        }
        ?>
        </form>
        <?php
            $pagination = $this->pagination( $terms_count, $cur_page, ( int )$terms_per_page );
            echo $pagination;
        ?>
    </div>
    
    <?php
    //creating terms table
    ?>
    
    <table class="wp-list-table widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" class="td-check-column">
                    <label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All', 'terms-descriptions' ); ?></label>
                    <input id="cb-select-all-1" class="cb-select-all" type="checkbox">
                </th>
                <th scope="col"><?php _e( 'Term', 'terms-descriptions' ); ?></th>
                <th scope="col"><?php _e( 'Term Link', 'terms-descriptions' ); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th scope="col" class="td-check-column">
                    <label class="screen-reader-text" for="cb-select-all-2"><?php _e( 'Select All', 'terms-descriptions' ); ?></label>
                    <input id="cb-select-all-2" class="cb-select-all" type="checkbox">
                </th>
                <th scope="col"><?php _e( 'Term', 'terms-descriptions' ); ?></th>
                <th scope="col"><?php _e( 'Term Link', 'terms-descriptions' ); ?></th>
            </tr>
        </tfoot>
        <tbody>
<?php
    $nonce = wp_create_nonce( 'td_delete_term' );
    
    $from = ( $cur_page - 1 ) * $terms_per_page;
    $to = $terms_per_page;
    $terms = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'td_terms ' . $where_clause . ' ORDER BY t_id DESC LIMIT ' . $from . ',' . $to );
    
    if ( is_array( $terms ) ) {
        foreach ( $terms as $term ) {
?>
            <tr id="term_<?php echo $term->t_id; ?>">
                <th scope="row" class="check-column">
                    <label class="screen-reader-text" for="cb-select-<?php echo $term->t_id; ?>"><?php _e( 'Select', 'terms-descriptions' ); ?></label>
                    <input id="cb-select-<?php echo $term->t_id; ?>" type="checkbox" name="td_term[]" value="<?php echo $term->t_id; ?>">
                    <div class="locked-indicator"></div>
                </th>
                <td>
                    <strong><?php echo stripcslashes( $term->t_term ); ?></strong>
                    <div class="row-actions">
                        <span class="edit"><a href="?action=td_edit_term&amp;term_id=<?php echo $term->t_id; ?>"><?php _e( 'Edit', 'terms-descriptions' ); ?></a> | </span>
                        <span class="trash"><a href="?action=td_delete_term&amp;term_id=<?php echo $term->t_id; ?>&amp;_wpnonce=<?php echo $nonce; ?>"><?php _e( 'Delete', 'terms-descriptions' ); ?></a></span>
                    </div>
                </td>
                <td><?php echo '<a href="' . $term->t_post_url . '" target="_blank">' . stripcslashes( $term->t_post_title ) . '</a>'; ?></td>
            </tr>
<?php
        }
    }
?>
        </tbody>
    </table>
    <div class="tablenav bottom">
        <?php echo $pagination; ?>
    </div>
    <div style="display: none;" id="td_update_permalinks_dialog">
        <p><?php _e( 'Premalinks updated', 'terms-descriptions' ); ?>: <span id="td_update_progress">0</span>%</p>
    </div>
</div>
<?php        
    }
    
    /**
     * This methos creates pagination links for terms table
     *
     * @param int $terms_count number of the terms
     * @param int $cur_page current page number
     * @param int $terms_per_page number of the terms on each page
     * @return string 
     */
    public function pagination( $terms_count, $cur_page, $terms_per_page ) {
        $total_pages = ceil( $terms_count / $terms_per_page );

        $first_page_link = add_query_arg( 'term_page', false );

        $prev_disabled = '';
        if ( $cur_page <= 1 ) {
            $prev_page_link = add_query_arg( 'term_page', false );
            $prev_disabled = ' disabled';
        }
        else {
            $prev_page_link = add_query_arg( 'term_page', $cur_page - 1 );
        }

        $last_page_link = add_query_arg( 'term_page', $total_pages );

        $next_disabled = '';
        if ( $cur_page >= $total_pages ) {
            $next_page_link = add_query_arg( 'term_page', $total_pages );
            $next_disabled = ' disabled';
        }
        else {
            $next_page_link = add_query_arg( 'term_page', $cur_page + 1 );
        }

        $html = '<div class="tablenav-pages">';
        $html .= '<span class="pagination-links">';
        if (!empty($prev_disabled)) {
            $html .= '<span class="tablenav-pages-navspan">«</span> ';
            $html .= '<span class="tablenav-pages-navspan">‹</span> ';
        }
        else {
            $html .= '<a class="first-page" title="' . esc_attr__( 'Go to the first page' ) . '" href="' . $first_page_link . '">«</a> ';
            $html .= '<a class="prev-page" title="' . esc_attr__( 'Go to the previous page' ) . '" href="' . $prev_page_link . '">‹</a> ';
        }
        $html .= '<span class="paging-input">';
        $html .= '<span class="total-pages"> ' . $cur_page . '</span> ' . __('of') . ' <span class="total-pages">' . $total_pages . ' </span>';
        $html .= '</span>';
        if (!empty($next_disabled)) {
            $html .= '<span class="tablenav-pages-navspan">›</span> ';
            $html .= '<span class="tablenav-pages-navspan">»</span> ';
        }
        else {
            $html .= '<a class="next-page" title="' . esc_attr__( 'Go to the next page' ) . '" href="' . $next_page_link . '">›</a> ';
            $html .= '<a class="last-page" title="' . esc_attr__( 'Go to the last page' ) . '" href="' . $last_page_link . '">»</a> ';
        }
        $html .= '</span>';
        $html .= '</div>';

        return $html;
    }
}

$tdat = new SCO_TD_Admin_Terms();