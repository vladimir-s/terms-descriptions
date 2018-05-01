<?php
require_once 'mockpress/mockpress.php';
require_once '../includes/parsers/td_parser.php';
require_once '../includes/parsers/td_simple_parser.php';
require_once '../includes/parsers/td_long_terms_first_parser.php';

class TD_Long_Terms_First_Test extends PHPUnit_Framework_TestCase {
    public $parser;

    public function setUp() {
        global $post;
		$post = (object)array('ID' => 1);
        $this->parser = $this->getMockBuilder('SCO_TD_Long_Terms_First_Parser')->setMethods(array('is_current_url'))->getMock();
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
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( '|тест|тесты|тестов|' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( '  |тест|тесты|тестов| ' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( '  |тест|||тесты||тестов| ' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( '  |тест|  | |тесты||тестов| ' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( 'тест|тесты|тестов|' ) ) );
        $this->assertEquals( $term, $pt->invokeArgs( $this->parser, array( 'тест|тесты|тестов|  ' ) ) );
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
                't_post_type' => 'post',
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

        $p_2 = $this->getMockBuilder('SCO_TD_Long_Terms_First_Parser')->setMethods(array('is_current_url'))->getMock();;
        $terms_2 = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            ),
            array(
                't_term' => 'анимацией|анимации',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            ),
        );
        $p_2->set_terms($terms_2);

        $orig_file_2 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/2.txt'));
        $parsed_file_2 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/2_1.txt'));
        $this->assertEquals($parsed_file_2, $p_2->parse($orig_file_2, 2));

        $p_3 = $this->getMockBuilder('SCO_TD_Long_Terms_First_Parser')->setMethods(array('is_current_url'))->getMock();
        $terms_3 = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            ),
            array(
                't_term' => 'анимацией|анимации',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            ),
            array(
                't_term' => 'Maya',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            ),
        );
        $p_3->set_terms($terms_3);

        $orig_file_3 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3.txt'));
        $parsed_file_3 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3_1.txt'));
        $this->assertEquals($parsed_file_3, $p_3->parse($orig_file_3, 1));

        $orig_file_5 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/4.txt'));
        $parsed_file_5 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/4_1.txt'));
        $this->assertEquals($parsed_file_5, $p_3->parse($orig_file_5, 1));

        $p_4 = $this->getMockBuilder('SCO_TD_Long_Terms_First_Parser')->setMethods(array('is_current_url'))->getMock();;
        $terms_4 = array(
        );
        $p_4->set_terms($terms_4);

        $orig_file_4 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3.txt'));
        $parsed_file_4 = str_replace(array("\r\n", "\n", "\r"), '', file_get_contents('texts/3_1.txt'));
        $this->assertEquals($orig_file_4, $p_4->parse($orig_file_4, 1));

        $p_6 = $this->getMockBuilder('SCO_TD_Long_Terms_First_Parser')->setMethods(array('is_current_url'))->getMock();
        $terms_6 = array(
            array(
                't_term' => "Zachary's Jewelers",
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            )
        );
        $p_6->set_terms($terms_6);
        $orig_text = "fdsgsfg Zachary's Jewelers впівп";
        $parsed_text = "fdsgsfg <a href=\"http://fdgd.sff\">Zachary's Jewelers</a> впівп";
        $this->assertEquals($parsed_text, $p_6->parse( $orig_text ) );

        $p_7 = $this->getMockBuilder('SCO_TD_Long_Terms_First_Parser')->setMethods(array('is_current_url'))->getMock();
        $terms_7 = array(
            array(
                't_term' => "test",
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            )
        );
        $p_7->set_terms($terms_7);
        $orig_text_1 = "fdsgsfg <a href='http://fdgd.sff'>test</a> впівп";
        $this->assertEquals($orig_text_1, $p_7->parse( $orig_text_1, '-1', false, -1, false, '<strong>', '</strong>', '', true ) );
        $orig_text_2 = "fdsgsfg <a href='http://fdgd.sff'>test</a> test впівп";
        $this->assertEquals($orig_text_2, $p_7->parse( $orig_text_2, '1', false, -1, false, '<strong>', '</strong>', '', true ) );
    }

    public function testSortTerms() {
        $terms = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            ),
            array(
                't_term' => 'деревянный стол',
                't_post_url' => 'http://ddd.sff',
                't_post_id' => 21,
                't_post_type' => 'post',
            )
        );
        $orig_text = 'xc xsf деревянный стол впівп';
        $parsed_text = 'xc xsf <a href="http://ddd.sff">деревянный стол</a> впівп';
        $this->parser->set_terms( $terms );
        $this->assertEquals($parsed_text, $this->parser->parse( $orig_text ) );
    }

    public function testNoIndex() {
        $terms = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'ext_link',
            )
        );
        $orig_text = 'xc xsf стол впівп';
        $parsed_text = 'xc xsf <noindex><a href="http://fdgd.sff">стол</a></noindex> впівп';
        //testing without terms
        $this->assertEquals($orig_text, $this->parser->parse( $orig_text, '-1', false, -1, false, '', '', '', 'on' ) );
    }

    public function testNoFollow() {
        $terms = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'ext_link',
            )
        );
        $orig_text = 'xc xsf стол впівп';
        $parsed_text = 'xc xsf <a href="http://fdgd.sff" rel="nofollow">стол</a> впівп';
        //testing without terms
        $this->assertEquals($orig_text, $this->parser->parse( $orig_text, '-1', false, -1, false, '', '', 'on' ) );
    }

    public function testWrap() {
        $terms = array(
            array(
                't_term' => 'стол',
                't_post_url' => 'http://fdgd.sff',
                't_post_id' => 22,
                't_post_type' => 'post',
            )
        );
        $orig_text = 'xc xsf стол впівп';
        $parsed_text = 'xc xsf <strong><a href="http://fdgd.sff">стол</a></strong> впівп';
        //testing without terms
        $this->assertEquals($orig_text, $this->parser->parse( $orig_text, '-1', false, -1, false, '<strong>', '</strong>' ) );
    }

	public function testQuotes() {
		$terms = array(
			array(
				't_term' => 'ресторан "Град Петров"',
				't_post_url' => 'http://fdgd.sff',
				't_post_id' => 22,
				't_post_type' => 'post',
			)
		);
		$orig_text = 'abc ресторан “Град Петров“ строительная компания';
		$parsed_text = 'abc <a href="http://fdgd.sff">ресторан “Град Петров“</a> строительная компания';

		$this->parser->set_terms( $terms );
		$this->assertEquals($parsed_text, $this->parser->parse( $orig_text ) );

		$terms1 = array(
			array(
				't_term' => 'ресторан «Град Петров»',
				't_post_url' => 'http://fdgd.sff',
				't_post_id' => 22,
				't_post_type' => 'post',
			)
		);
		$orig_text1 = 'abc ресторан “Град Петров“ строительная компания';
		$parsed_text1 = 'abc <a href="http://fdgd.sff">ресторан “Град Петров“</a> строительная компания';

		$this->parser->set_terms( $terms1 );
		$this->assertEquals($parsed_text1, $this->parser->parse( $orig_text1 ) );

		$terms2 = array(
			array(
				't_term' => 'типография ‘Град Петров’',
				't_post_url' => 'http://fdgd.sff',
				't_post_id' => 22,
				't_post_type' => 'post',
			)
		);
		$orig_text2 = 'abc типография “Град Петров’ строительная компания';
		$parsed_text2 = 'abc <a href="http://fdgd.sff">типография “Град Петров’</a> строительная компания';

		$this->parser->set_terms( $terms2 );
		$this->assertEquals($parsed_text2, $this->parser->parse( $orig_text2 ) );
	}

    protected static function getMethod( $name ) {
        $class = new ReflectionClass('SCO_TD_Long_Terms_First_Parser');
        $method = $class->getMethod( $name );
        $method->setAccessible( true );
        return $method;
    }
}
