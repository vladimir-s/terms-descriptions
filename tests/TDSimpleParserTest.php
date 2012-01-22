<?php
require_once '../includes/parsers/td_parser.php';
require_once '../includes/parsers/td_simple_parser.php';

class TD_Simple_Parser_Test extends PHPUnit_Framework_TestCase {
    public $parser;
    
    public function setUp() {
        global $post;
        $post->ID = 1;
        $this->parser = $this->getMock('TD_Simple_Parser', array('is_current_url'));
    }
    
    public function testPrepareTermRegex() {
        $ptr = self::getMethod( 'prepare_term_regex' );
        $this->assertEquals( 'abc', $ptr->invokeArgs( $this->parser, array( 'abc' ) ) );
        $this->assertEquals( '', $ptr->invokeArgs( $this->parser, array( '' ) ) );
        $this->assertEquals( 4, strlen('a\&d') );
        $this->assertEquals( 1, strlen('\'') );
        $this->assertEquals( 2, strlen('\\\'') );
        $this->assertEquals( 2, strlen('\"') );
        $this->assertEquals( 'a\&d', $ptr->invokeArgs( $this->parser, array( 'a&d' ) ) );
        $this->assertEquals( 'abcsdf\sdsf\ssdfg', $ptr->invokeArgs( $this->parser, array( 'abcsdf dsf sdfg' ) ) );
        $this->assertEquals( '\sпри\.Вет\s', $ptr->invokeArgs( $this->parser, array( ' при.Вет ' ) ) );
        $this->assertEquals( '\sп\,ри\.В\*\*ет\s', $ptr->invokeArgs( $this->parser, array( ' п,ри.В**ет ' ) ) );
        $this->assertEquals( '\s\sп\(ри\)\.Вет\s', $ptr->invokeArgs( $this->parser, array( '  п(ри).Вет ' ) ) );
        $this->assertEquals( '\s\sпри\.\{Ве\}т\s', $ptr->invokeArgs( $this->parser, array( '  при.{Ве}т ' ) ) );
        $this->assertEquals( '\^\^\s\sпри\.\{Ве\}\?т\s', $ptr->invokeArgs( $this->parser, array( '^^  при.{Ве}?т ' ) ) );
        $this->assertEquals( '\s\sп\#\#р\$и\.\{Ве\}т\s\$', $ptr->invokeArgs( $this->parser, array( '  п##р$и.{Ве}т $' ) ) );
    }
    
    public function testPrepareTerm() {
        $pt = self::getMethod( 'prepare_term' );
        $this->assertEquals( false, $pt->invokeArgs( $this->parser, array( null ) ) );
        $this->assertEquals( false, $pt->invokeArgs( $this->parser, array( false ) ) );
        $this->assertEquals( false, $pt->invokeArgs( $this->parser, array( 7989 ) ) );
        $this->assertEquals( false, $pt->invokeArgs( $this->parser, array( '' ) ) );
        $this->assertEquals( false, $pt->invokeArgs( $this->parser, array( '   ' ) ) );
        
        $term = array( 'тест', 'тесты', 'тестов' );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( 'тест|тесты|тестов' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( '  тест|тесты|тестов ' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( ' тест  |тесты |тестов ' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( " тест | тесты\t| тестов" ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( " тест | тесты | тестов \n" ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( "\tтест | тесты|тестов\r\n" ) ) );
    }
    
    public function testSetTerms() {
        $terms = array(
            array( 't_term' => array( 'тест', 'тесты', 'тестов' ) ),
            array( 't_term' => array( 'сто\.л', 'сто\.ла', 'сто\.лов' ) ),
            array( 't_term' => array( '\(пирог\)', '\(пирогов\)' ) ),
        );
        $strings = array(
            array( 't_term' => 'тест|тесты|тестов' ),
            array( 't_term' => 'сто.л|сто.ла|сто.лов' ),
            array( 't_term' => ' (пирог) | (пирогов)' ),
        );
        
        $this->parser->set_terms( $strings );
        $this->assertEquals( $terms, $this->parser->get_terms() );

        $this->parser->set_terms( '' );
        $this->assertEquals( $terms, $this->parser->get_terms() );
        
        $this->parser->set_terms( 'ds dsf sdf' );
        $this->assertEquals( $terms, $this->parser->get_terms() );

        $this->parser->set_terms( array( 't_term' => 564 ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );

        $this->parser->set_terms( array( 't_term' => null ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );

        $this->parser->set_terms( array( 't_term' => false ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );

        $strings_1 = array(
            array( 't_term' => ' (пирог) | (пирогов)' ),
        );
        
        $this->parser->set_terms( $strings_1 );
        $this->assertEquals( array( $terms[ 2 ] ), $this->parser->get_terms() );
    }
    
    public function testAddTerm() {
        $terms = array();
        
        $this->parser->add_term( array( 't_term' => '' ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );
        
        $terms = array(
            array( 't_term' => array( 'сто\{л\}', 'сто\{ла\}', 'сто\{лов\}' ) ),
        );
        $this->parser->add_term( array( 't_term' => ' сто{л} | сто{ла}|сто{лов}' ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );
        
        $terms[] = array( 't_term' => array( 'вишня' ) );
        $this->parser->add_term( array( 't_term' => "вишня\r\n" ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );
        
        $terms[] = array( 't_term' => array( 'слива', 'слив' ) );
        $this->parser->add_term( array( 't_term' => "\nслива\t|слив  " ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );
        
        $terms[] = array( 't_term' => array( 'Большие\sворота', 'Больших\sворот' ) );
        $this->parser->add_term( array( 't_term' => "Большие ворота | Больших ворот" ) );
        $this->assertEquals( $terms, $this->parser->get_terms() );
    }
    
    public function testParse() {
        $terms = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            )
        );
        $orig_text = 'xc xsf стол впівп';
        $parsed_text = 'xc xsf <a href="http://fdgd.sff">стол</a> впівп';
        //testing without terms
        $this->assertEquals($orig_text, $this->parser->parse( $orig_text ) );
        
        $this->parser->set_terms( $terms );
        $this->assertEquals($parsed_text, $this->parser->parse( $orig_text ) );
        
        $orig_file = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/1.txt'));
        $parsed_file = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/1_1.txt'));
        $this->assertEquals($parsed_file, $this->parser->parse($orig_file));
        
        $p_2 = $this->getMock('TD_Simple_Parser', array('is_current_url'));
        $terms_2 = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            ),
            array(
                't_term' => 'анимацией|анимации',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            ),
        );
        $p_2->set_terms($terms_2);
        
        $orig_file_2 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/2.txt'));
        $parsed_file_2 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/2_1.txt'));
        $this->assertEquals($parsed_file_2, $p_2->parse($orig_file_2, 2));
        
        $p_3 = $this->getMock('TD_Simple_Parser', array('is_current_url'));
        $terms_3 = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            ),
            array(
                't_term' => 'анимацией|анимации',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            ),
            array(
                't_term' => 'Maya',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            ),
        );
        $p_3->set_terms($terms_3);
        
        $orig_file_3 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3.txt'));
        $parsed_file_3 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3_1.txt'));
        $this->assertEquals($parsed_file_3, $p_3->parse($orig_file_3, 1));

        $orig_file_5 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/4.txt'));
        $parsed_file_5 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/4_1.txt'));
        $this->assertEquals($parsed_file_5, $p_3->parse($orig_file_5, 1));
        
        $p_4 = $this->getMock('TD_Simple_Parser', array('is_current_url'));
        $terms_4 = array(
        );
        $p_4->set_terms($terms_4);
        
        $orig_file_4 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3.txt'));
        $parsed_file_4 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3_1.txt'));
        $this->assertEquals($orig_file_4, $p_4->parse($orig_file_4, 1));
    }
    
    public function testWrap() {
        $terms = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
            )
        );
        $orig_text = 'xc xsf стол впівп';
        $parsed_text = 'xc xsf <strong><a href="http://fdgd.sff">стол</a></strong> впівп';
        //testing without terms
        $this->assertEquals($orig_text, $this->parser->parse( $orig_text, '-1', false, -1, false, '<strong>', '</strong>' ) );
    }
    
    protected static function getMethod( $name ) {
        $class = new ReflectionClass( 'TD_Simple_Parser' );
        $method = $class->getMethod( $name );
        $method->setAccessible( true );
        return $method;
    }
}
