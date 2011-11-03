<?php
/**
 * Simple parser.
 * Searches for terms in a text and converts them to links.
 */
class TD_Simple_Parser extends TD_Parser {
    //parser will ignore these tags
    protected $skip_tags = array(
        '<a\s.*?<\/a>',
        '<abbr\s.*?<\/abbr>',
        '<acronym\s.*?<\/acronym>',
        '<address.*?<\/address>',
        '<applet\s.*?<\/applet>',
        '<audio.*?<\/audio>',
        '<caption.*?<\/caption>',
        '<canvas.*?<\/canvas>',
        '<code.*?<\/code>',
        '<embed.*?<\/embed>',
        '<form\s.*?<\/form>',
        '<frame.*?<\/frame>',
        '<frameset.*?<\/frameset>',
        '<h\d>.*?<\/h\d>',
        '<iframe\s.*?<\/iframe>',
        '<map.*?<\/map>',
        '<noembed.*?<\/noembed>',
        '<noframes.*?<\/noframes>',
        '<noscript.*?<\/noscript>',
        '<object.*?<\/object>',
        '<pre.*?<\/pre>',
        '<samp.*?<\/samp>',
        '<script.*?<\/script>',
        '<style.*?<\/style>',
        '<video.*?<\/video>',
        '<.*?>',
    );
    
    /**
     * Searches for terms in a text and converts them to links.
     *
     * @param string $text original text
     * @param string $replace_terms number of terms to replace
     * @param string $class_attr CSS class that will be added to term link
     * @return string parsed text
     */
    public function parse( $text, $replace_terms = '-1', $class_attr = false ) {
        if ( null !== $text && !empty( $text ) ) {
            if ( $class_attr !== false && trim( $class_attr ) !== '' ) {
                $class_attr = ' class="' . $class_attr . '"';
            }
            
            foreach ( $this->terms as $term ) {
                //regular expression for deviding post context
                //(devision is made by html tags)
                preg_match_all( '/' . implode( '|', $this->skip_tags ) . '/isu', $text,
                    $matches, PREG_OFFSET_CAPTURE );
                $start_pos = 0;

                //regular expression for term replacement
                $term_search_str = implode( '|', $term[ 't_term' ] );
                $replace_re = '/([\s\r\n\:\;\!\?\.\,\)\(<>]{1}|^)(' . $term_search_str
                        . ')([\s\r\n\:\;\!\?\.\,\)\(<>]{1}|$)/isu';

                $result = '';
                $terms_count = $replace_terms;
                
                //adding links to terms
				foreach ( $matches[0] as $match ) {
					//is their a text before this occuarance?
					$length = $match[1] - $start_pos;
					if ( $length > 0 ) {
						//searching for a term
						$fragment = substr( $text, $start_pos, $length );
						$result .= $this->replace_term( $replace_re, $term, $fragment, $terms_count, $class_attr );
					}
					//adding html tag to the result
					$result .= $match[0];
					$start_pos = $match[1] + strlen( $match[0] );
				}
				//cheking if all post content was parsed
				//(problem may occur if the closing tag in post content was missed)
				if ( $start_pos < strlen( $text )) {
					$fragment = substr( $text, $start_pos );
                    $result .= $this->replace_term( $replace_re, $term, $fragment, $terms_count, $class_attr );
				}
				$text = $result;
            }
        }
        return $text;
    }
    
    /**
     * Replaces terms with links.
     *
     * @param string $replace_re
     * @param array $term current term
     * @param string $text original text
     * @param int $terms_count number of currently replaced terms
     * @param string $class_attr CSS class that will be added to term link 
     * @return string processed text 
     */
    protected function replace_term( $replace_re, $term, $text, &$terms_count, $class_attr ) {
        $result = '';
        //if user set replacements number, we execute nesessary number of replacements
        if ( (int)$terms_count > 0 ) {
            if ( 0 < preg_match( $replace_re, $text ) ) {
                $result = preg_replace( $replace_re, '$1<a href="'. $term[ 't_post_url' ]
                        .'"'.$class_attr.'>$2</a>$3', $text, $terms_count, $replaced );
                $terms_count -= $replaced;
            }
            else {
                $result = $text;
            }
        }
        //if $terms_count === -1 (unlimited) we replace all terms occurrences
        elseif ( (int)$terms_count === -1 ) {
            if ( 0 < preg_match( $replace_re, $text ) ) {
                $result = preg_replace( $replace_re, '$1<a href="'. $term[ 't_post_url' ]
                        . '"' . $class_attr . '>$2</a>$3', $text );
            }
            else {
                $result = $text;
            }
        }
        //otherwise, return the original text without replacements
        else {
            return $text;
        }
        return $result;
    }
}