<?php
/**
 * Admin
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

class Admin {

	/**
	 * Magic method construct
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function create_admin_menu(): void {
		add_menu_page(
			esc_html__( 'Just Odds Settings', 'justodds' ),
			'Just Odds',
			'manage_options',
			'justodds_settings',
			array( $this, 'create_admin_page' ),
			'dashicons-welcome-widgets-menus',
			90
		);
	}

	public function register_settings(): void {

		register_setting(
			'justodds_settings',
			'justodds_settings',
			array( $this, 'validate' )
		);

		add_settings_section(
			'status',
			esc_html__('Status', 'justodds' ),
			array( $this, 'status' ),
			'justodds_settings'
		);

		add_settings_section(
			'group-events',
			'Group Events',
			array( $this, 'group_events' ),
			'justodds_settings'
		);

		add_settings_section(
			'single-events',
			'Event Information',
			array( $this, 'single_events' ),
			'justodds_settings'
		);
	}

	public function group_events(): void {

		$json_events = ( new Fetch() )->get_json( 'event-group-ids.json', '/data/sports/horseracingwew' );
		$events      = array();
		$total       = count( $json_events );

		foreach( $json_events as $event ) {

			$event_name = str_replace( ' ', '-', $event['name'] );
			$event_name = str_replace( '(', '', $event_name );
			$event_name = str_replace( ')', '', $event_name );

			$events[] = sprintf(
				'<tr>
					<td>%s</td>
					<td>%s</td>
					<td><code>%s</code></td>
				</tr>',
				$event['name'],
				$event['id'],
				'[justodds event="' . strtolower( $event_name ) . '" group="true"]'
			);
		}

		printf(
			'<table class="justodds-table">%s %s</table>',
			sprintf(
				'<thead>%s %s %s</thead>',
				'<th>' . esc_html__( 'Name', 'justodds' ) . ' (' . $total . ') </th>',
				'<th>' . esc_html__( 'ID', 'justodds' ) . '</th>',
				'<th>' . esc_html__( 'Shortcode', 'justodds' ) . '</th>',
			),
			sprintf(
				'<tbody>%s</tbody>',
				implode( '', $events )
			)
		);
	}

	public function single_events(): void {

		$json_events = ( new Fetch() )->get_json( 'events.json', '/data/sports/horseracingwew' );
		$events = array();
		$total       = count( $json_events );

		foreach( $json_events as $event ) {

			$event_name = str_replace( ' ', '-', $event['name'] );
			$event_name = str_replace( '(', '', $event_name );
			$event_name = str_replace( ')', '', $event_name );
			$event_name = str_replace( '&', '', $event_name );
			$event_name = str_replace( 'amp;', '', $event_name );

			$events[] = sprintf(
				'<tr>
					<td>%s</td>
					<td>%s</td>
					<td><code>%s</code></td>
				</tr>',
				$event['name'],
				$event['id'],
				'[justodds event="' . strtolower( $event_name ) . '"]'
			);
		}

		printf(
			'<table class="justodds-table">%s %s</table>',
			sprintf(
				'<thead>%s %s %s</thead>',
				'<th>' . esc_html__( 'Name', 'justodds' ) . ' (' . $total . ') </th>',
				'<th>' . esc_html__( 'ID', 'justodds' ) . '</th>',
				'<th>' . esc_html__( 'Shortcode', 'justodds' ) . '</th>',
			),
			sprintf(
				'<tbody>%s</tbody>',
				implode( '', $events )
			)
		);
	}

	/**
	 * Options validate
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $input
	 * @return array
	 */
	public function validate( $input ): array {

		$new_input['bet365_affiliate_code'] = trim( $input['bet365_affiliate_code'] );

		return $new_input;
	}

	public function is_curl() {
		return function_exists('curl_version');
	}

	public function status(): void {

        $status = ( new Connect() )->status();
        $curl   = $this->is_curl();

        var_dump( $curl );

        if ( true === $status ) {
	        $bet365_status = sprintf(
		        '<span class="justodds-status justodds-status--connected">%s</span>',
		        esc_html__( 'Connected to Bet365 feeds', 'justodds' )
	        );
        } else {
	        $bet365_status = sprintf(
		        '<span class="justodds-status justodds-status--disconnected">%s</span>',
		        esc_html__( 'Unable to connect to Bet365 feeds', 'justodds' )
	        );
        }

		if ( true === $curl ) {
			$curl_status = sprintf(
				'<span class="justodds-status justodds-status--connected">%s</span>',
				esc_html__( 'CURL is Enabled', 'justodds' )
			);
		} else {
			$curl_status = sprintf(
				'<span class="justodds-status justodds-status--disconnected">%s</span>',
				esc_html__( 'CURL is Disabled', 'justodds' )
			);
		}

        printf(
                '<table class="justodds-table">
               <thead>%s</thead>
               <tbody>%s %s</tbody>
            </table>',
            sprintf(
                   '  <tr>
                        <th>%s</th>
                        <th>%s</th>
                    </tr>',
	            esc_html__( 'Item', 'justodds' ),
	            esc_html__( 'Status', 'justodds' )
            ),
            sprintf(
               '<tr>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>',
	            'Bet365',
	            $bet365_status
            ),
	        sprintf(
		        '<tr>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>',
		        'Curl',
		        $curl_status
	        ),
        );
	}

	/**
	 * Create admin page
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @return void
	 */
	public function create_admin_page(): void {
		?>
		<h2><?php esc_html_e( 'JustOdds - Settings', 'justodds' ); ?></h2>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'justodds_settings' );
			do_settings_sections( 'justodds_settings' ); ?>
        </form>

		<?php
	}
}