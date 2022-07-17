<?php
/**
 * Conversion class
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
 * @since   0.1.0
 *
 * @package JustOdds\App
 */
class Connect {

	/**
	 * Site URI
	 *
	 * @since 0.1.0
	 *
	 * @acces public
	 * @var string Save uri value.
	 */
	public string $uri;

	/**
	 * Construct method.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param   string  $uri
	 */
	public function __construct( string $uri = 'oddsfeed2.bet365.com' ) {
		$this->uri = $uri;
	}

	/**
	 * Simple curl request to primary domain in order to validate if the server can request files
	 * from the restricted IP.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return bool If connection was successful or not.
	 */
	public function status(): bool {

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $this->uri );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$output = curl_exec( $ch );

		if ( ! $output ) {
			return false;
		}

		curl_close( $ch );

		return true;
	}

	/**
	 * Method to return status information in order to be displayed to the
	 * administrators of the site.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Return statement based on status.
	 */
	public function status_info(): string {
		if ( false === $this->status() ) {
			return esc_html__( 'Unable to connect to Bet365 feeds', 'justodds' );
		}

		return esc_html__( 'Connected to Bet365 feeds', 'justodds' );
	}

	/**
	 * Fetch data from a url.
	 *
	 * @since  0.1.0
	 *
	 * @access public
	 *
	 * @param   string  $value Item value.
	 * @param   string  $type  Type of output response xml/html.
	 *
	 * @return object|string   Return response of data requested.
	 */
	public function fetch( string $value, string $type = 'xml' ) {

		if ( false === $this->status()) {
			return false;
		}

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, trailingslashit( $this->uri ) . $value );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

		$response = curl_exec( $ch );

		try {
			if ( $type === 'xml' ) {
				return new \SimpleXMLElement( $response );
			}
			return $response;
		} catch ( \Exception $e ) {
			return $e->getMessage();
		}
	}
}