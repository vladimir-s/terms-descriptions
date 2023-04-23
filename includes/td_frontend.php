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

        //applying additional filters
        if ( !empty( $additional_filters = $this->options->getOption( 'additional_filters' ) ) ) {
            $additional_filters_lines = explode( "\n", $additional_filters );
            foreach ( $additional_filters_lines as $filter_name ) {
                $trimmed_filter_name = trim( $filter_name );
                if ( !empty( $trimmed_filter_name ) && !in_array( $trimmed_filter_name, ['the_content', 'comment_text', 'get_the_archive_description'] ) ) {
                    add_filter( $trimmed_filter_name, array( $this, 'parse_content' ) );
                }
            }
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
     * @param bool $is_td_shortcode whether parsing [terms-descriptions] shortcode
     * @return string updated post content
     */
    public function parse_content( $content, $is_td_shortcode = false ) {

        global $wpdb, $post;

        if ( false === $this->check_parse_conditions( $is_td_shortcode ) ) {
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

        if (!empty($post->post_type)) {
            foreach ($terms as $key => $term) {
                $convert_in_post_types = (!empty($term['t_use_in_post_types'])) ? unserialize($term['t_use_in_post_types'])
                    : $term['t_use_in_post_types'];
                if (is_array($convert_in_post_types) && !empty($convert_in_post_types)
                    && !in_array($post->post_type, $convert_in_post_types)) {

                    unset($terms[$key]);
                }
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
            , $this->options->getOption( 'add_nofollow' ), $this->options->getOption( 'add_noindex' )
            , $this->options->getOption( 'skip_noindex_nofollow_for_internal' ) );
    }

    /**
     * Decide to parse or not.
     *
     * @global type $post
     * @param  bool $is_td_shortcode
     * @return boolean true = do parse
     */
    public function check_parse_conditions( $is_td_shortcode = false ) {

        global $post;

        // Is convert_only_single option set AND this is not a single or page?
        if ( $this->options->getOption( 'convert_only_single' ) && !is_single() && !is_page() ) {
            return false;
        }

        // Is TD disabled for this specific post?
        if ( false === $is_td_shortcode && 'on' === get_post_meta( $post->ID, '_disable_terms_descriptions', true ) ) {
            return false;
        }

        // Is converting this post-type AND converting in shortcodes is disabled?
        if ( 'on' !== $this->options->getOption( 'convert_in__'.$post->post_type )
            && 'on' !== $this->options->getOption( 'convert_in_shortcodes' ) ) {
            return false;
        }

        return true;
    }
}

$tdf = new SCO_TD_Frontend();