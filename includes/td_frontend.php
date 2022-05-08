<?php
/**
 * This class parses post content and replaces terms with links.
 */
class SCO_TD_Frontend {
    private $options;
    
    /**
     * Constuctor. Sets the filters handlers.
     */
    public function __construct() {
        $this->options = SCO_TD_Options::getInstance();

        //post filter
        $priority = 10;
        if ( 'on' === $this->options->getOption( 'convert_in_shortcodes' ) ) {
            $priority = 12;
        }
        add_filter( 'the_content', array( $this, 'parse_content' ), $priority );
        //comments filter
        if ( 'on' === $this->options->getOption( 'convert_in_comments' ) ) {
            add_filter( 'comment_text', array( $this, 'parse_content' ) );
        }
        //convert in archive descriptions
        if ( 'on' === $this->options->getOption( 'convert_archive_descriptions' ) ) {
            add_filter( 'get_the_archive_description', array( $this, 'parse_content' ) );
        }

        add_shortcode('terms-descriptions', function ( $atts, $content = "" ) {
            return $this->parse_content( $content, true );
        });
    }

    /**
     * This method replaces terms with links in post content.
     *
     * @global wpdb $wpdb wordpress database class
     * @param string $content original post content
     * @return string updated post content
     */
    public function parse_content( $content, $is_td_shortcode = false ) {
        //checking if have to convert terms on this page
        if ( false === $this->options->getOption( 'convert_only_single' ) || is_single() || is_page() ) {
            global $wpdb, $post;
            if ( false === $is_td_shortcode && 'on' === get_post_meta( $post->ID, '_disable_terms_descriptions', true ) ) {
                return $content;
            }
	        if ( 'on' !== $this->options->getOption( 'convert_in__'.$post->post_type )
	             && 'on' !== $this->options->getOption( 'convert_in_posts' ) ) {
		        return $content;
	        }
            //selecting parser
            switch ( $this->options->getOption( 'parser' ) ) {
                case 'simple_parser' :
                    $parser = new SCO_TD_Simple_Parser();
                    break;
                case 'quotes_parser' :
                    $parser = new SCO_TD_Simple_Quotes_Parser();
                    break;
                case 'long_terms_first_parser' :
                    $parser = new SCO_TD_Long_Terms_First_Parser();
                    break;
                default :
                    $parser = new SCO_TD_Simple_Parser();
                    break;
            }
            //getting the terms
            $terms = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'td_terms ORDER BY t_id DESC', ARRAY_A );
            foreach ( $terms as $key => $term ) {
                $convert_in_post_types = ( !empty( $term['t_use_in_post_types'] ) ) ? unserialize( $term['t_use_in_post_types'] ) : $term['t_use_in_post_types'];
                if ( is_array( $convert_in_post_types ) && !empty( $convert_in_post_types )
                    && !in_array( $post->post_type, $convert_in_post_types ) ) {

                        unset( $terms[$key] );
                }
            }

            //setting up parser
            $parser->set_terms( $terms );
            $parser->add_skip_tags( $this->options->getOption( 'skip_tags' ) );
            //target attribute
            $target = '';
            if ( 'on' === $this->options->getOption( 'open_new_tab' ) ) {
                $target = ' target="_blank" ';
            }

            $consider_existing_links = false;
            if ( 'on' === $this->options->getOption( 'consider_existing_links' ) ) {
                $consider_existing_links = true;
            }

            //replacing terms
            return $parser->parse( $content, $this->options->getOption( 'convert_first_n_terms' ), $this->options->getOption( 'class' )
                    , ( int )$this->options->getOption( 'convert_total' ), $this->options->getOption( 'show_title' )
                    , $this->options->getOption( 'text_before' ), $this->options->getOption( 'text_after' ), $target, $consider_existing_links
                    , $this->options->getOption( 'add_nofollow' ), $this->options->getOption( 'add_noindex' ) );
        }
        else {
            return $content;
        }
    }
}

$tdf = new SCO_TD_Frontend();