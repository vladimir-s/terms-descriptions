<?php
/**
 * Base parser class.
 * All parsers must be inherited from this class.
 */
abstract class TD_Parser {
    protected $counters = array();
    protected $terms = array();
    private $cur_url = '';
    
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
        $term = preg_quote( $term, '/' );
        $search = array( ' ', '\'', '"', '&', ',', '#' );
        $replace = array( '\s', '\\\'', '\"', '\&', '\,', '\#' );
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

    /**
     * Checks if current post ID is equal to the first parameter value
     *
     * @param $id post ID
     * @return bool true if current post ID is equal to the ID
     */
    protected function is_current_post( $id ) {
        global $post;
        return ( $post->ID === ( int )$id ) ? true : false;
    }

    /**
     * Compares current page URL with given URL
     *
     * @param $url given URL
     * @return bool true if URLs are equals
     */
    protected function is_current_url( $url ) {
        return $this->get_current_url() === trailingslashit( $url );
    }

    /**
     * This function is returns current page URL
     *
     * @return string current page URL
     */
    private function get_current_url() {
        if ( $this->cur_url !== '' ) {
            return $this->cur_url;
        }
        $this->cur_url = 'http';
        if ( $_SERVER[ "HTTPS" ] == "on" ) {
            $this->cur_url .= "s";
        }
        $this->cur_url .= "://";
        if ( $_SERVER[ "SERVER_PORT" ] != "80" ) {
            $this->cur_url .= $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ];
        } else {
            $this->cur_url .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
        }
        return trailingslashit( $this->cur_url );
    }
}