<?php
/**
 * Automatically locates and loads files based on their namespaces and file names.
 * Instantiates the Autoloader, and registers it with the standard PHP library.
 *
 * @author      Karl Adams <karl.adams@drunkmosquito.com>
 * @copyright   Copyright (c) 2022, Drunk Mosquito Ltd
 * @link        https://www.drunkmosquito.com
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since       0.1.0
 * @package     justodds
 */

spl_autoload_register( static function ( $file ) {

	// First, separate the components of the incoming file.
	$file_path = explode( '\\', $file );

	/**
	 * - The first index will always be the namespace since it's part of the plugin.
	 * - All but the last index will be the path to the file.
	 * - The final index will be the filename. If it doesn't begin with 'I' then it's a class.
	 */

	// Get the last index of the array. This is the class we're loading.
	$file_name = '';

	if ( isset( $file_path[ count( $file_path ) - 1 ] ) ) {

		$file_name       = strtolower( $file_path[ count( $file_path ) - 1 ] );
		$file_name       = str_ireplace( '_', '-', $file_name );
		$file_name_parts = explode( '-', $file_name );
		$index           = $file_name_parts[0];

		if ( 'interface' === $index || 'trait' === $index ) {

			// Remove the 'interface' part.
			unset( $file_name_parts[ $index ] );

			// Rebuild the file name.
			$file_name = implode( '-', $file_name_parts );
			$file_name = $file_name . '.php';

		} else {
			$file_name = "class-$file_name.php";
		}
	}

	/**
	 * Find the fully qualified path to the class file by iterating through the $file_path array.
	 * We ignore the first index since it's always the top-level package. The last index is always
	 * the file. Therefore, we append that at the end.
	 *
	 * @since 0.1.0
	 * @var  string Qualified path.
	 */
	$fully_qualified_path = trailingslashit( dirname( __FILE__, 2 ) );

	for ( $i = 1; $i < count( $file_path ) - 1; $i ++ ) {
		$dir                  = strtolower( $file_path[ $i ] );
		$fully_qualified_path .= trailingslashit( $dir );
	}

	$fully_qualified_path .= $file_name;

	if ( stream_resolve_include_path( $fully_qualified_path ) ) {
		include_once $fully_qualified_path;
	}
} );
