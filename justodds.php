<?php
/**
 * Just Odds bootstrap file
 *
 * This file is used to generate all plugin information. Including all of the
 * dependencies used by the plugin, registers the activation and deactivation
 * functions, and defines a function that starts the plugin.
 *
 * @author     Karl Adams <karl.adams@drunkmosquito.com>
 * @copyright   Copyright (c) 2022, Drunk Mosquito Ltd
 * @license     http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link        https://www.drunksmoquito.com
 * @package     justodds
 * @since       0.1.0
 *
 * @wordpress-plugin
 * Plugin Name:       Just Odds
 * Plugin URI:        https://www.drunkmosquito.com
 * Description:       Fetches and displays odds for bet365
 * Version:           0.1.0
 * Requires at Least: 5.7
 * Requires PHP:      7.4
 * Author:            Karl Adams <karl.adams@drunkmosquito.com>
 * Author URI:        https://www.drunkmosquito.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain        justodds
 */

namespace JustOdds;

use JustOdds\App;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

require_once 'lib/autoload.php';
require_once 'includes/simple_html_dom.php';

add_action(
	'plugins_loaded',
	function () {

		/**
		 * Create Core Defaults object.
		 *
		 * @since 1.0.0
		 * @var   \JustOdds\App\Defaults
		 */
		$defaults = new App\Defaults();

		( new App\Languages() )->run(
			$defaults->get_constant( 'text_domain' )
		);

		new App\Admin();
		new App\Output();
		new App\ShortCodes();
	}
);