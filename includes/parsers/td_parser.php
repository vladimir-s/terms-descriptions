<?php
/**
 * Base parser class.
 * All parsers must be inherited from this class.
 */
abstract class TD_Parser {
    protected $counters = array();
    protected $terms = array();
    protected $cur_url = '';
    
    /**
     * Parses the text
     */
    abstract public function parse( $text );
    
    /**
     * Sets the terms
     * 
     * @param array $terms array of the terms with links
     */
    public function set_terms( $terms ) {
        if ( is_array( $terms ) ) {
            $new_terms = array();
            foreach ( $terms as $i => $term ) {
                //we are using term only if we can prepare it
                if ( isset( $term[ 't_term' ] )
                        && false !== ( $prepared_term = $this->prepare_term( $term[ 't_term' ] ) ) ) {
                    $new_terms[ $i ] = $term;
                    $new_terms[ $i ][ 't_term' ] = $prepared_term;
                }
            }
            if ( !empty( $new_terms ) ) {
                $this->terms = $new_terms;
            }
        }
    }
    
    /**
     * Adds the term.
     *
     * @param array $term term data
     */
    public function add_term( $term ) {
        if ( is_array( $this->terms ) ) {
            //we are using term only if we can prepare it
            if ( isset( $term[ 't_term' ] )
                    && false !== ( $prepared_term = $this->prepare_term( $term[ 't_term' ] ) ) ) {
                $term[ 't_term' ] = $prepared_term;
                $this->terms[] = $term;
            }
        }
    }
    
    /**
     * Returns array of terms with their data
     * @return array terms data
     */
    public function get_terms() {
        return $this->terms;
    }
    
    /**
     * This mathod escapes special symbols in term that can break a regular expression.
     *
     * @param string $term original term
     * @return string escaped term
     */
    protected function prepare_term_regex( $term ) {
        $search = array( ' ', '.', '\'', '"', '-', '&', ')', '(', '[', ']', '+', '{', '}', '*', ',', '^', '?', '$', '#' );
        $replace = array( '\s', '\.', '\\\'', '\"', '\-', '\&', '\)', '\(', '\[', '\]', '\+', '\{', '\}', '\*', '\,', '\^', '\?', '\$', '\#' );
        return str_replace( $search, $replace, $term );
    }
    
    /**
     * This method converts term string in array with the term word forms and
     * prepares them for using in regular expressions.
     *
     * @param string $term original term
     * @return array terms
     */
    protected function prepare_term( $term ) {
        if ( $term === null || !is_string( $term ) ) {
            return false;
        }
        
        $trimmed_term = trim( $term );
        if ( empty( $trimmed_term ) ) {
            return false;
        }
        
        $words = explode( '|', $trimmed_term );
        
        if ( false === $words || !is_array( $words ) ) {
            return false;
        }
        
        foreach ( $words as $i => $word ) {
            $words[ $i ] = $this->prepare_term_regex( trim( $word ) );
        }
        return $words;
    }

    protected function is_current_post( $id ) {
        global $post;
        return ( $post->ID === ( int )$id ) ? true : false;
    }
}