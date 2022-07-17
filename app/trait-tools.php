<?php
/**
 * Trait Tools.
 *
 * @author      Karl Adams <karl.adams@drunkmosquito.com>
 * @copyright   Copyright (c) 2022, Drunk Mosquito Ltd
 * @link        https://www.drunkmosquito.com
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since       0.1.0
 * @category    JustOdds\Core
 * @package     JustOdds
 */

namespace JustOdds\App;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

/**
 * Trait Trait_Tools
 *
 * @package JustOdds\App
 */
trait Trait_Tools {

	/**
	 * Plugin accepted values list.
	 *
	 * @since  0.1.0
	 *
	 * @access protected
	 * @static
	 * @var    array Values listed in plugin heading.
	 */
	protected array $accepted_values = array(
		'Name',
		'PluginURI',
		'Description',
		'Version',
		'Author',
		'AuthorURI',
		'TextDomain',
		'DomainPath',
		'Network',
	);

	/**
	 * Plugin data
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @var    array Array of plugin elements.
	 */
	public array $constants;

	/**
	 * Plugin Database version.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected string $db_version = '1.0.0';

	/**
	 * Vendors directory.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected string $vendors = '\\vendors';

	/**
	 * Languages directory.
	 *
	 * @since 0.1.0
	 *
	 * @access protected
	 * @var    string
	 */
	protected string $languages = '\\languages';

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since 0.1.0
	 *
	 * @access  private
	 * @param   string $type Type of dependencies.
	 * @uses    get_files(), get_plugin_path(), trailingslashit().
	 * @return void
	 */
	private function get_dependencies( string $type ): void {

		$core_files = $this->get_files(
			trailingslashit( $this->get_plugin_path() . $type ),
			array( 'php' )
		);

		foreach ( $core_files as $file ) {
			require_once $file;
		}

		unset( $file );
	}

	/**
	 * Retrieve plugin basename.
	 *
	 * @since   0.1.0
	 *
	 * @access  public
	 * @return  string plugin basename.
	 */
	public function get_plugin_basename(): string {
		return plugin_basename( $this->get_plugin_path() );
	}

	/**
	 * Retrieve path for sub directory.
	 *
	 * @since   0.1.0
	 *
	 * @access  public
	 * @return  string includes constant.
	 */
	public function get_sub_dir_path( string $dir_name ): string {
		return $this->get_plugin_path() . $dir_name;
	}

	/**
	 * Method to collect files from a given directory and store them into an
	 * array.
	 *
	 * @since 0.1.0
	 *
	 * @acces  private
	 * @param  array $dir       Source dir to be scanned.
	 * @param  array $extension Extensions allowed.
	 * @param  array $files     Array with results.
	 * @return array            Results.
	 */
	private function get_files( array $dir, array $extension, array $files = array() ): array {
		foreach ( scandir( $dir ) as $content ) {
			$ext = substr( $content, strrpos( $content, '.' ) + 1 );
			if ( in_array( $ext, $extension, true ) ) {
				$files[] = $dir . $content;
			}
		}

		return $files;
	}

	/**
	 * Return the database version of the plugin.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Return plugin database version number.
	 */
	public function get_plugin_db_version(): string {
		return $this->db_version;
	}

	/**
	 * Retrieve plugin path.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Plugin path.
	 */
	public static function get_plugin_path(): string {
		return dirname( __DIR__ );
	}

	/**
	 * Retrieve plugin url.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return string Plugin url.
	 */
	public static function get_plugin_url(): string {
		return esc_url( plugin_dir_url( __DIR__ ) );
	}

	/**
	 * Get plugin information from main file.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param string $value Value to get from plugin file data.
	 * @return string       Return plugin information
	 */
	public function get_plugin_info( string $value ): string {

		$data = array(
			'Name'        => 'Plugin Name',
			'PluginURI'   => 'Plugin URI',
			'Description' => 'Description',
			'Version'     => 'Version',
			'Author'      => 'Author',
			'AuthorURI'   => 'Author URI',
			'TextDomain'  => 'Text Domain',
			'DomainPath'  => 'Domain Path',
			'Network'     => 'Network',
		);

		$plugin_info = get_file_data(
			trailingslashit( $this->get_plugin_path() ) . $this->get_plugin_basename() . '.php',
			$data,
			'plugin'
		);

		return $plugin_info[ $value ];
	}

	/**
	 * Returns the version number of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param  string $request Plugin information value request.
	 * @return string          Return plugin version number.
	 */
	public function get_plugin_info_value( string $request ): string {
		if ( in_array( $request, $this->accepted_values, true ) ) {
			return $this->get_plugin_info( $request );
		}

		return '';
	}

	/**
	 * Get constant value
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param  string $request Return plugin value.
	 */
	public function get_constant( string $request ) {
		return $this->constants[ $request ];
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since 0.1.0
	 *
	 * @access private
	 * @return void
	 */
	private function set_constants(): void {

		$name = $this->get_plugin_info_value( 'Name' );
		$name = strtolower( $name );

		$this->constants = array(
			'name'        => str_replace( ' ', '-', $name ),
			'version'     => $this->get_plugin_info_value( 'Version' ),
			'basename'    => $this->get_plugin_basename(),
			'db-version'  => $this->get_plugin_db_version(),
			'dir'         => $this->get_plugin_path(),
			'url'         => $this->get_plugin_url(),
			'uri'         => $this->get_plugin_info_value( 'PluginURI' ),
			'author'      => $this->get_plugin_info_value( 'Author' ),
			'lang'        => $this->get_sub_dir_path( $this->languages ),
			'vendors'     => $this->get_sub_dir_path( $this->vendors ),
			'text_domain' => $this->get_plugin_info_value( 'TextDomain' ),
			'assets'      => trailingslashit( $this->get_plugin_url() . 'assets' ),
			'media'       => trailingslashit( $this->get_plugin_url() . 'media' ),
			'timeout'     => apply_filters( $name . '_timeout', 60 ),
		);
	}
}