<?php
/**
 * Defaults
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
 * Primary class for setting up the plugin.
 *
 * @since   1.0.0
 *
 * @package JustOdds\App
 */
class Defaults {
	use Trait_Tools;

	/**
	 * Class construct method.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct() {
		$this->set_constants();
		$this->set_locale();

		add_action( 'wp_enqueue_scripts', array( $this, 'set_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'set_assets_admin' ) );
	}

	/**
	 * Set locale
	 *
	 * @since  0.1.0
	 *
	 * @access private
	 * @return void
	 */
	private function set_locale(): void {
		add_action(
			'plugins_loads',
			array( __NAMESPACE__ . $this->languages, 'load_plugin_text_domain' )
		);
	}

	public function set_assets(): void {
		wp_enqueue_style(
			'justodds',
			$this->get_plugin_url() . '/assets/css/justodds.css',
			array(),
			null,
			'all'
		);
	}

	public function set_assets_admin(): void {
		wp_enqueue_style(
			'justodds-admin',
			$this->get_plugin_url() . 'assets/css/justodds-admin.css',
			array(),
			null,
			'all'
		);
	}
}