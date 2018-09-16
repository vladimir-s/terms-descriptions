<?php
/**
 * This class creates meta boxes on post edit screen
 */
class SCO_TD_Meta_Box {
    /**
     * Constuctor. Sets the actions handlers.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
        add_action( 'save_post', array( $this, 'save_postdata' ) );
    }
    
    public function add_boxes() {
        $types = get_post_types( array (
            'public' => true,
            'show_ui' => true
        ), 'names' );
        
        foreach ( $types as $type ) {
            add_meta_box(
                'terms_descriptions_settings',
                __( 'Terms Descriptions', 'terms-descriptions' ),
                array( $this, 'show_box' ),
                $type,
                'normal',
                'low'
            );
        }
    }
    
    public function show_box() {
        wp_nonce_field( 'disable_terms_descriptions', '_td_nonce' );
        $cur_value = get_post_meta( get_the_ID(), '_disable_terms_descriptions', true );
?>
<label for="disable_terms_descriptions">
<?php _e( 'Disable Terms Descriptions plugin for this post', 'terms-descriptions' ); ?>
    <input type="checkbox" name="disable_terms_descriptions" id="disable_terms_descriptions" <?php checked( $cur_value, 'on' ) ?> />
</label>
<?php
    }
    
    public function save_postdata( $post_id ) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if ( !isset($_POST[ '_td_nonce' ]) || !wp_verify_nonce( $_POST[ '_td_nonce' ], 'disable_terms_descriptions' ) ) {
            return;
        }

        // Check permissions
        if ( 'page' == $_POST[ 'post_type' ] ) {
            if ( !current_user_can( 'edit_page', $post_id ) )
                return;
        }
        else {
            if ( !current_user_can('edit_post', $post_id ) )
                return;
        }

        if (isset($_POST[ 'disable_terms_descriptions' ])) {
            update_post_meta( $post_id, '_disable_terms_descriptions', $_POST[ 'disable_terms_descriptions' ] );
        }
        else {
            delete_post_meta( $post_id, '_disable_terms_descriptions' );
        }
    }
}
$tdmb = new SCO_TD_Meta_Box();