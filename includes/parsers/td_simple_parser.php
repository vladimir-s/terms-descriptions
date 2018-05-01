<?php
/**
 * Simple parser.
 * Searches for terms in a text and converts them to links.
 */
class SCO_TD_Simple_Parser extends SCO_TD_Parser {
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
        '<h\d.*?>.*?<\/h\d>',
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
    
    protected $max_convertions = null;
    
    /**
     * Searches for terms in a text and converts them to links.
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
        if ( null !== $text && !empty( $text ) ) {
            if ( $class_attr !== false && trim( $class_attr ) !== '' ) {
                $class_attr = ' class="' . $class_attr . '"';
            }
            
            if ( -1 === $max_convertions ) {
                $this->max_convertions = null;
            }
            else {
                $this->max_convertions = $max_convertions;
            }
            
            if ( is_int( $this->max_convertions ) && $this->max_convertions <= 0 ) {
                return $text;
            }

            foreach ( $this->terms as $term ) {
                //if page URL or ID is equal to term ID or URL - skipping term
                if ( $this->is_current_post( $term[ 't_post_id' ] ) ) {
                    continue;
                }
                if ( isset( $term[ 't_post_type' ] ) && $term[ 't_post_type' ] === 'ext_link'
                        && $this->is_current_url( $term[ 't_post_url' ] ) ) {
                    continue;
                }

                // t_post_title was equal to t_post_url before titles support for external links was added
                if ( $show_title === 'on' && $term[ 't_post_title' ] !== $term[ 't_post_url' ] ) {
                    $title_attr = ' title="' . esc_attr( wp_kses_stripslashes( $term[ 't_post_title' ] ) ) . '" ';
                }
                else {
                    $title_attr = '';
                }
                
                //regular expression for dividing post context
                //(division is made by html tags)
                preg_match_all( '/' . implode( '|', $this->skip_tags ) . '/isu', $text,
                    $matches, PREG_OFFSET_CAPTURE );
                $start_pos = 0;

                //regular expression for term replacement
                $term_search_str = implode( '|', $term[ 't_term' ] );
                $replace_re = '/([\s\r\n\:\;\!\?\.\,\)\(<>]{1}|^)(' . $term_search_str
                        . ')([\s\r\n\:\;\!\?\.\,\)\(<>]{1}|$)/isu';

                $result = '';
                $terms_count = $replace_terms;
                if ( $terms_count !== '-1' && true === $consider_existing_links ) {
                    $terms_count -= $this->find_existing_links( $text, $term );
                }

                if ( $term[ 't_post_type' ] === 'ext_link' ) {
                    if ( $add_nofollow === 'on' ) {
                        $target .= ' rel="nofollow"';
                    }
                    if ( $add_noindex === 'on' ) {
                        $text_before = '<noindex>'.$text_before;
                        $text_after = $text_after.'</noindex>';
                    }
                }

                //adding links to terms
                foreach ( $matches[0] as $match ) {
                    //is their a text before this occurrence?
                    $length = $match[1] - $start_pos;
                    if ( $length > 0 ) {
                        //searching for a term
                        $fragment = substr( $text, $start_pos, $length );
                        $result .= $this->replace_term( $replace_re, $term, $fragment
                                , $terms_count, $class_attr, $title_attr, $text_before
                                , $text_after, $target );
                    }
                    //adding html tag to the result
                    $result .= $match[0];
                    $start_pos = $match[1] + strlen( $match[0] );
                    
                    if ( is_int( $this->max_convertions ) && $this->max_convertions <= 0 ) {
                        break;
                    }
                }
                //checking if all post content was parsed
                //(problem may occur if the closing tag in post content was missed)
                if ( $start_pos < strlen( $text )) {
                    $fragment = substr( $text, $start_pos );
                    if ( is_int( $this->max_convertions ) && $this->max_convertions <= 0 ) {
                        $result .= $fragment;
                    }
                    else {
                        $result .= $this->replace_term( $replace_re, $term, $fragment
                                , $terms_count, $class_attr, $title_attr, $text_before
                                , $text_after, $target );
                    }
                }
                $text = $result;
                
                if ( is_int( $this->max_convertions ) && $this->max_convertions <= 0 ) {
                    break;
                }
            }
        }
        return $text;
    }

    /**
     * Appends $tags to $this->skip_tags array
     *
     * @param $tags string|array tags string
     */
    public function add_skip_tags( $tags ) {
        if ( !is_array( $tags ) ) {
            $tags = trim( $tags );
            if ( '' === $tags ) {
                return;
            }
            $tags = explode( '|', trim( $tags ) );
            foreach ( $tags as $key => $tag ) {
                if ( '' !== trim( $tag ) ) {
                    $tags[ $key ] = trim( $tag );
                }
            }
        }
        if ( count( $tags ) > 0 ) {
            $this->skip_tags = array_merge( $tags, $this->skip_tags );
        }
    }
    
    /**
     * Replaces terms with links.
     *
     * @param string $replace_re
     * @param array $term current term
     * @param string $text original text
     * @param int $terms_count number of currently replaced terms
     * @param string $class_attr CSS class that will be added to term link 
     * @param string $title_attr title attribute that will be added to term link
     * @param string $target link target attribute
     * @return string processed text 
     */
    protected function replace_term( $replace_re, $term, $text, &$terms_count, $class_attr
            , $title_attr = '', $text_before = '', $text_after = '', $target = '' ) {
        $result = '';

        if ( null !== $this->max_convertions && ( int )$terms_count > $this->max_convertions ) {
            $terms_count = $this->max_convertions;
        }

        //if user set replacements number, we execute necessary number of replacements
        if ( (int)$terms_count > 0 ) {
            if ( 0 < preg_match( $replace_re, $text ) ) {
                $result = preg_replace( $replace_re, '$1' . $text_before . '<a href="'. $term[ 't_post_url' ]
                        . '"' . $class_attr . $title_attr . $target . '>$2</a>' . $text_after . '$3', $text, $terms_count, $replaced );
                $terms_count -= $replaced;
            }
            else {
                $result = $text;
            }
        }
        //if $terms_count === -1 (unlimited) we replace all terms occurrences
        elseif ( (int)$terms_count === -1 ) {
            if ( 0 < preg_match( $replace_re, $text ) ) {
                $result = preg_replace( $replace_re, '$1' . $text_before . '<a href="'. $term[ 't_post_url' ]
                        . '"' . $class_attr . $title_attr . $target . '>$2</a>' . $text_after . '$3', $text, -1, $replaced );
            }
            else {
                $result = $text;
            }
        }
        //otherwise, return the original text without replacements
        else {
            return $text;
        }
        
        if ( isset($replaced) && null !== $replaced && null !== $this->max_convertions ) {
            $this->max_convertions -= $replaced;
        }
        
        return $result;
    }
}