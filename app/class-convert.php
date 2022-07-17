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
class Convert {
	use Trait_Tools;

	/**
	 * XML to JSON conversion.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param  object  $xml XML page object.
	 * @return false|string
	 * @throws \JsonException
	 */
	public function xml_to_json( object $xml ) {
		return json_encode( $xml, JSON_THROW_ON_ERROR );
	}

	/**
	 * Array to JSON.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param  $array
	 * @return false|string
	 * @throws \JsonException
	 */
	public function array_to_json( $array ) {

		if ( $array === null ) {
			return null;
		}

		return json_encode( $array, JSON_THROW_ON_ERROR );
	}

	/**
	 * Save JSON file.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param           $data
	 * @param   string  $file_name
	 * @param   string  $location
	 *
	 * @return false|void
	 */
	public function save_json( $data, string $file_name, string $location ) {

		if ( $data === null ) {
			return false;
		}

		$dir = trailingslashit( $this->get_plugin_path() . '/' . $location );
		file_put_contents( $dir . $file_name, $data );
	}
}