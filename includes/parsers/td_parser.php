<?php
/**
 * Base parser class.
 * All parsers must be inherited from this class.
 */
abstract class SCO_TD_Parser {
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
        $term = str_replace('\\\'', '\'', $term);
        $term = preg_quote( $term, '/' );
        $search = array( ' ', '&', ',', '#' );
        $replace = array( '\s', '\&', '\,', '\#' );
	    $term = str_replace( $search, $replace, $term );
        return preg_replace( '/(\"|\\\'|\“|\”|\‘|\’|\«|\»)/i', '[\"\\\'\“\”\‘\’\«\»]', $term );
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
		
		//removing empty terms
		foreach ( $words as $key => $word ) {
			$w = trim($word);
			if ( empty($w) ) {
				unset( $words[$key] );
			}
		}
		$words = array_values($words);
        
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
	    $cur_slug = $this->get_current_url();
	    # remove anchor
        $url = preg_replace( '/#[a-zA-Z0-9-_%\/]*$/i', '', $url );
	    $term_url = preg_replace( '/^https?\:\/\/(www\.)?/i', '', trailingslashit( $url ) );
        return $cur_slug === $term_url;
    }

    /**
     * Counts URLs occurrences in the text
     *
     * @param $text original text
     * @param $term term data (must contain t_post_url field)
     * @return int number of URL in the text
     */
    protected function find_existing_links( $text, $term ) {
        $url = $term[ 't_post_url' ];
        if ( substr( $term[ 't_post_url' ], -1 ) === '/' ) {
            $url = substr( $term[ 't_post_url' ], 0, count( $term[ 't_post_url' ] ) - 2 );
        }
        return preg_match_all( '/' . str_replace( '/', '\/', preg_quote( $url ) ) . '\/?(\"|\'|\s)/i', $text, $matches );
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
        if ( !in_array( $_SERVER[ "SERVER_PORT" ], array( "80", "443" ) ) ) {
            $this->cur_url = trailingslashit( $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ] );
        } else {
            $this->cur_url = trailingslashit( $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ] );
        }
        return $this->cur_url;
    }
}