<?php
/**
 * Languages
 *
 * @author      Karl Adams <karl.adams@drunkmosquito.com>
 * @copyright   Copyright (c) 2022, Drunk Mosquito Ltd
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link        https://www.drunkmosquito.com
 * @package     JustOdds
 * @category    JustOdds\Core
 * @since       0.1.0
 */

namespace JustOdds\App;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

/**
 * Languages class
 *
 * @since 0.1.0
 *
 * @package JustOdds\App
 */
class Languages {

	private string $text_domain;

	/**
	 * Run method for languages.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param  string  $text_domain Language text domain
	 * @return void
	 */
	public function run( string $text_domain ): void {
		$this->text_domain = $text_domain;
		add_action( 'plugins_loaded', array( $this, 'load_plugin_text_domain' ) );
	}

	/**
	 * Current language check. Used WPML built-in function to check language
	 * value.
	 *
	 * @since  0.1.0
	 *
	 * @access public
	 * @return mixed
	 */
	public function language_check(): mixed {
		if ( function_exists(  'icl_object_id' ) ) {
			return apply_filters( 'wpml_current_language', NULL );
		} else {
			return esc_html__(  'WPML Not active', 'c4-plus' );
		}
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since  0.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_text_domain(): void {
		load_plugin_textdomain(
			$this->text_domain,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/'
		);
	}
}