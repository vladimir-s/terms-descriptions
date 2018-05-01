<?php
class SCO_TD_Options {
	private $options = array();
	private static $instance = null;

	private function __construct() {
		$this->options = get_option('td_options');
		$this->prepareOptions();
	}

	public static function getInstance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function prepareOptions() {
		if ( !isset( $this->options[ 'text_before' ] ) ) {
			$this->options[ 'text_before' ] = '';
		}
		if ( !isset( $this->options[ 'text_after' ] ) ) {
			$this->options[ 'text_after' ] = '';
		}
		if ( !isset( $this->options[ 'add_nofollow' ] ) ) {
			$this->options[ 'add_nofollow' ] = '';
		}
		if ( !isset( $this->options[ 'add_noindex' ] ) ) {
			$this->options[ 'add_noindex' ] = '';
		}
		if ( !isset( $this->options[ 'skip_tags' ] ) ) {
			$this->options[ 'skip_tags' ] = '';
		}
	}

	public function getOption( $name ) {
		if ( !isset( $this->options[ $name ] ) ) {
			return false;
		}
		return $this->options[ $name ];
	}
}