<?php
/**
 * This class creates Tools page in Terms menu
 */
class TD_Admin_Tools {
    private $page = '';
    private $message = '';
    
    /**
     * Constuctor. Sets the actions handlers.
     */
    public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'init', array( $this, 'process_form_data' ) );
    }
    
    /**
     * This method checks whether the form is sent and processed it.
     */
    public function process_form_data() {
        //export
        if ( isset( $_POST[ 'td_export' ] ) && !empty ( $_POST[ 'td_export' ] ) ) {
            $this->export();
        }
        //import
        if ( isset( $_POST[ 'td_import' ] ) && !empty ( $_POST[ 'td_import' ] ) ) {
            if (false === $this->import() ) {
                $this->message = __( 'Import error', TD_TEXTDOMAIN );
            }
            else {
                $this->message = __( 'Import successfully finished', TD_TEXTDOMAIN );
            }
        }
        //packet upload
        if ( isset( $_POST[ 'td_packet_upload' ] ) && !empty ( $_POST[ 'td_packet_upload' ] ) ) {
            if (false === $this->packet_upload() ) {
                $this->message = __( 'Terms creation error', TD_TEXTDOMAIN );
            }
            else {
                $this->message = __( 'Terms successfully created', TD_TEXTDOMAIN );
            }
        }
    }
    
    /**
     * This method creates Tools page
     */
    public function admin_menu() {
        $this->page = add_submenu_page( TD_TEXTDOMAIN, __( 'Tools', TD_TEXTDOMAIN)
                , __( 'Tools', TD_TEXTDOMAIN), 'manage_options', 'td-tools'
                , array( $this, 'tools_page' ));
		add_action( 'admin_print_styles-' . $this->page, array( $this, 'load_styles' ) );
    }
    
    /**
     * This method includes CSS file
     */
    public function load_styles() {
        wp_enqueue_style( 'td_css', TD_URL . '/css/td_styles.css' );
    }
    
    /**
     * This method renders Tools page
     */
    public function tools_page() {
?>
<div class="wrap">
	<h2><?php _e( 'Tools', TD_TEXTDOMAIN); ?></h2>
    <?php if ( !empty( $this->message ) ) { ?>
        <div id="setting-error-settings_updated" class="updated settings-error"> 
            <p><strong><?php echo $this->message; ?></strong></p>
        </div>
    <?php } ?>
    <h3><?php _e( 'Export / Import', TD_TEXTDOMAIN ); ?></h3>
    <form action="admin.php?page=td-tools" method="post" class="tools_form">
        <input type="submit" name="td_export" value="<?php _e( 'Export', TD_TEXTDOMAIN ); ?>" class="button-primary" />
    </form>
    <form action="admin.php?page=td-tools" method="post" enctype="multipart/form-data" class="tools_form">
        <?php wp_nonce_field( 'td_import' ); ?>
        <label><?php _e( 'Select file with terms', TD_TEXTDOMAIN ); ?> <input type="file" name="dump_file" /></label>
        <input type="submit" name="td_import" value="<?php _e( 'Import', TD_TEXTDOMAIN ); ?>" class="button-primary" />
    </form>
    <h3><?php _e( 'Packet terms upload', TD_TEXTDOMAIN ); ?></h3>
    <form action="admin.php?page=td-tools" method="post" class="tools_form">
        <?php wp_nonce_field( 'td_packet_upload' ); ?>
        <label><?php _e( 'Terms list', TD_TEXTDOMAIN ); ?> <textarea name="terms" rows="10" cols="40" class="large-text code"></textarea></label>
        <div class="description"><?php _e( 'Each term should be written on its own line. Use the following format.'
                .'<br />word_form_1|word_form_2|...|post_id OR URL (with http://)'
                .'<br />Examples:'
                .'<br />apple|apples|21'
                .'<br />pear|pears|http://site.domen<br />'
                .'Note that if you use term_id the post with this id should exist.', TD_TEXTDOMAIN ); ?></div>
        <input type="submit" name="td_packet_upload" value="<?php _e( 'Add terms', TD_TEXTDOMAIN ); ?>" class="button-primary" />
    </form>
</div>
<?php        
    }
    
    /**
     * This method creates file with plugin data in JSON format
     *
     * @global type $wpdb wordpress database class
     */
    public function export() {
        header( 'Content-Type:application/json' );
        $file_name = 'terms_descriptions_' . date( 'Y_m_d_G_i_s' ) . '.json';
        header( 'Content-Disposition:attachment; filename="' . $file_name . '"' );
        global $wpdb;
        
        $terms = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix. 'td_terms' );
        
        $data = array();
        if ( is_array( $terms ) && !empty( $terms ) ) {
            $data = json_encode($terms);
        }
        
        echo $data;
        
        exit();
    }
    
    /**
     * This method loads data from backup file in JSON format
     *
     * @global type $wpdb wordpress database class
     * @return boolean true if import was successful and false otherwise 
     */
    public function import() {
        if( !wp_verify_nonce( $_POST[ '_wpnonce' ], 'td_import' ) ) {  
          return false;  
        }
        
        if( !empty( $_FILES[ 'dump_file' ][ 'name' ] ) ) {
            //loading data from backup file
            $data = json_decode( file_get_contents( $_FILES[ 'dump_file' ][ 'tmp_name' ] ) );

            if ( NULL !== $data && is_array( $data ) ) {
                global $wpdb;
                //saving terms
                $insert_sql = 'INSERT INTO ' . $wpdb->prefix . 'td_terms VALUES (null,%d,%s,%s,%s,%s)';
                foreach ( $data as $term ) {
                    if ( isset( $term->t_post_id ) && isset( $term->t_post_title )
                            && isset( $term->t_post_url ) && isset( $term->t_post_type )
                            && isset( $term->t_term ) ) {
                        $wpdb->query( $wpdb->prepare( $insert_sql, $term->t_post_id, $term->t_post_title
                                , $term->t_post_url, $term->t_post_type, $term->t_term ) );
                    }
                }
                return true;
            }
        }
        return false;
    }
    
    /**
     * This method parses list of terms and creates them.
     *
     * @global type $wpdb wordpress database class
     * @return boolean true if upload was successful and false otherwise 
     */
    public function packet_upload() {
        //checking form data
        if( !wp_verify_nonce( $_POST[ '_wpnonce' ], 'td_packet_upload' ) ) {  
          return false;  
        }
        
        if ( !isset( $_POST[ 'terms' ] ) || empty( $_POST[ 'terms' ] ) ) {
            return false;
        }
        
        $terms = explode( "\n", $_POST[ 'terms' ] );
        
        if ( !is_array( $terms ) ) {
            return false;
        }
        
        //processing terms
        foreach ( $terms as $term ) {
            $term_parts = explode( '|', $term );
            //removing spaces
            foreach ( $term_parts as $i => $part ) {
                $term_parts[ $i ] = trim( $part );
            }
            if ( count( $term_parts ) < 2 ) {
                continue;
            }
            
            $term_data = array();
            $term_data[ 't_term' ] = implode( '|', array_slice( $term_parts, 0, count( $term_parts ) - 1 ) );
            
            $link = $term_parts[ count( $term_parts ) - 1 ];
            
            //checking terms links
            if ( false !== ( $link_data = $this->check_link( $link ) ) ) {
                $term_data = array_merge( $link_data, $term_data );
                
                //saving term
                global $wpdb;
                $wpdb->insert( $wpdb->prefix . 'td_terms', $term_data, array( '%d', '%s', '%s', '%s', '%s' ) );
                if ( !is_int( $wpdb->insert_id ) || ( int )$wpdb->insert_id <= 0 ) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Checks link and forms array with link data
     *
     * @param string $link original link
     * @return mixed array with link data or false for none proper link
     */
    private function check_link( $link ) {
        $res = array();
        //if link is post id
        if ( 1 === preg_match( '/^\d+$/i', $link ) ) {
            $id = (int)$link;
            //trying to find the post
            $post = get_post( $id, ARRAY_A );
            if ( $post === null ) {
                return false;
            }
            $res[ 't_post_id' ] = $post[ 'ID' ];
            $res[ 't_post_title' ] = $post[ 'post_title' ];
            $res[ 't_post_type' ] = $post[ 'post_type' ];
            $res[ 't_post_url' ] = get_permalink( $id );
            return $res;
        }
        //if link is external URL
        elseif (1 === preg_match( '/^\w{3,5}\:\/\//i', $link ) ) {
            $res[ 't_post_id' ] = 0;
            $res[ 't_post_title' ] = $link;
            $res[ 't_post_type' ] = 'ext_link';
            $res[ 't_post_url' ] = $link;
            return $res;
        }
        else {
            return false;
        }
    }
}

$tdat = new TD_Admin_Tools();