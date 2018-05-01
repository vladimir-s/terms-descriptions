<?php
/**
 * Simple parser which tries to convert long terms first.
 * Searches for terms in a text and converts them to links.
 */
class SCO_TD_Long_Terms_First_Parser extends SCO_TD_Simple_Parser {

    /**
     * Orders terms array by descending of their lengths, then searches for terms in a text and converts them to links.
     *
     * @param string $text original text
     * @param string $replace_terms number of terms to replace
     * @param string $class_attr CSS class that will be added to term link
     * @param int $max_convertions maximum number of terms-to-links conversions
     * @param boolean $show_title whether to show link title
     * @param string $target link target attribute
     * @return string parsed text
     */
    public function parse( $text, $replace_terms = '-1', $class_attr = false
            , $max_convertions = -1, $show_title = false
            , $text_before = '', $text_after = '', $target = '', $consider_existing_links = false
            , $add_nofollow = false, $add_noindex = false ) {

        //sorting terms list according to terms length
        uasort( $this->terms, array('SCO_TD_Long_Terms_First_Parser', 'compare_terms' ) );

        return parent::parse($text, $replace_terms, $class_attr
            , $max_convertions, $show_title, $text_before, $text_after, $target
            , $consider_existing_links, $add_nofollow, $add_noindex);
    }

    /**
     * Compares terms length.
     *
     * @static
     * @param $t1 first term
     * @param $t2 second term
     * @return int returns 0 if terms lengths are equal, 1 - if second term is longer, -1 - otherwise
     */
    public static function compare_terms( $t1, $t2 ) {
        if ( strlen( $t1[ 't_term' ][ 0 ] ) === strlen( $t2[ 't_term' ][ 0 ] ) ) {
            return 0;
        }
        return ( strlen( $t1[ 't_term' ][ 0 ] ) < strlen( $t2[ 't_term' ][ 0 ] ) ) ? 1 : -1;
    }
}