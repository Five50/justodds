<?php
/**
 * Convert class
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
 * Convert data type.
 *
 * @since   1.0.0
 *
 * @package JustOdds\App
 */
class Fetch {
	use Trait_Tools;

	/**
	 * Get JSON
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param   string  $file
	 * @param   string  $location
	 *
	 * @return mixed|string|null
	 */
	public function get_json( string $file, string $location = '/data/sports' ) {

		$dir = trailingslashit( $this->get_plugin_path() . $location );

		if ( ! file_exists( $dir . $file ) ) {
			return null;
		}

		$data = file_get_contents( $dir . $file );

		try {
			return json_decode( $data, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Get sports list.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return array|string|null
	 */
	public function get_types(): array {
		return $this->get_json( 'sports.json', '/data' );
	}
}