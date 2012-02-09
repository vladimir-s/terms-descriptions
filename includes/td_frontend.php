<?php
/**
 * This class parses post content and replaces terms with links.
 */
class TD_Frontend {
    private $options;
    
    /**
     * Constuctor. Sets the filters handlers.
     */
    public function __construct() {
        $this->options = get_option( 'td_options' );
        if ( false !== $this->options ) {
            //post filter
            if ( true == $this->options[ 'convert_in_posts' ] ) {
                add_filter( 'the_content', array( $this, 'parse_content' ) );
            }
            //comments filter
            if ( true == $this->options[ 'convert_in_comments' ] ) {
                add_filter( 'comment_text', array( $this, 'parse_content' ) );
            }
        }
    }
    
    /**
     * This method replaces terms with links in post content.
     *
     * @global type $wpdb wordpress database class
     * @param string $content original post content
     * @return string updated post content 
     */
    public function parse_content( $content ) {
        //checking if have to convert terms on this page
        if ( false == $this->options[ 'convert_only_single' ] || is_single() || is_page() ) {
            global $wpdb, $post;
            if ( 'on' === get_post_meta( $post->ID, '_disable_terms_descriptions', true ) ) {
                return $content;
            }
            //selecting parser
            switch ( $this->options[ 'parser' ] ) {
                case 'simple_parser' :
                    $parser = new TD_Simple_Parser();
                    break;
                case 'quotes_parser' :
                    $parser = new TD_Simple_Quotes_Parser();
                    break;
                default :
                    $parser = new TD_Simple_Parser();
                    break;
            }
            //getting the terms
            $terms = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'td_terms ORDER BY t_id DESC', ARRAY_A );
            //setting up parser
            $parser->set_terms( $terms );
            //target attribute
            $target = '';
            if ( isset( $this->options[ 'open_new_tab' ] ) && 'on' === $this->options[ 'open_new_tab' ] ) {
                $target = ' target="_blank" ';
            }
            
            //replacing terms
            return $parser->parse( $content, $this->options[ 'convert_first_n_terms' ], $this->options[ 'class' ]
                    , ( int )$this->options[ 'convert_total' ], $this->options[ 'show_title' ]
                    , $this->options[ 'text_before' ], $this->options[ 'text_after' ], $target );
        }
        else {
            return $content;
        }
    }
}

$tdf = new TD_Frontend();