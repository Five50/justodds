<?php
/**
 * Shortcodes
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
 * Shortcodes class
 *
 * @since 0.1.0
 *
 * @package JustOdds\App
 */
class Output {

	/**
	 * Magic method contruct
	 *
	 * @since 0.1.0
	 * @access public
	 */
	public function __construct() {
		$this->get_events();
		$this->get_event_group_data();
		$this->get_event_single_data();
	}

	/**
	 * Get Results
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param   string  $handle
	 * @return void
	 */
	public function get_event_group_data( string $handle = 'horseracingwew' ): void {

		$event_groups = ( new Fetch() )->get_json( 'event-group-ids.json', '/data/sports/' . $handle );

		$location = dirname( __DIR__ ) . '/data/sports/' . $handle . '/events-group/';

		foreach( $event_groups as $event ) {
			if ( false === get_transient( 'justodds_event_group_' . $event['filename'] ) ) {
				$filename = $event['filename'] . '.json';

				$event_group_id = '&EventGroupID=' . $event['id'];
				$language_id    = '&LanguageID=1';

				$xml      = ( new Connect() )->fetch( $handle . '?' . $event_group_id . $language_id );
				$convert  = new Convert();

				if ( ! file_exists( $location ) ) {
					mkdir( $location, 0777, false, null );
				}

				try {
					$convert->save_json(
						$convert->array_to_json( $xml ),
						$filename,
						trailingslashit( 'data/sports/' . $handle . '/events-group/' )
					);
				} catch ( \JsonException $e ) {
					$e->getMessage();
				}

				set_transient(
					'justodds_event_group_' . $event['filename'],
					true,
					DAY_IN_SECONDS
				);
			}
		}
	}


	/**
	 * Get Results
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param   string  $handle
	 * @return void
	 */
	public function get_event_single_data( string $handle = 'horseracingwew' ): void {

		$events = ( new Fetch() )->get_json( 'events.json', '/data/sports/' . $handle );

		$location = dirname( __DIR__ ) . '/data/sports/' . $handle . '/events/';

		foreach( $events as $event ) {
			if ( false === get_transient( 'justodds_event_' . $event['filename'] ) ) {
				$filename = $event['filename'] . '.json';

				$event_id    = '&EventID=' . $event['id'];
				$language_id = '&LanguageID=1';

				$xml     = ( new Connect() )->fetch( $handle . '?' . $event_id . $language_id );
				$convert = new Convert();

				if ( ! file_exists( $location ) ) {
					mkdir( $location, 0777, false, null );
				}

				try {
					$convert->save_json(
						$convert->array_to_json( $xml ),
						$filename,
						trailingslashit( 'data/sports/' . $handle . '/events/' )
					);
				} catch ( \JsonException $e ) {
					$e->getMessage();
				}

				set_transient(
					'justodds_event_' . $event['filename'],
					true,
					DAY_IN_SECONDS
				);
			}
		}
	}

	/**
	 * Get events.
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param   int  $id Sport ID.
	 * @return void
	 */
	public function get_events( int $id = 2 ): void {

		$types = ( new Fetch )->get_types();

		$location = dirname( __DIR__ ) . '/data/sports/' . $types[ $id ]['handle'];

		if ( ! file_exists( $location ) ) {
			mkdir( $location, 0777, false, null );
		}

		if ( false === get_transient( 'justodds_' . $types[ $id ]['handle'] . '_events' ) ) {

			$connect = ( new Connect() )->fetch(
				'listevents?spid=' . $id . '&ip=0&lng=1',
				'html'
			);

			$html = new \simple_html_dom();
			$html->load( $connect );

			$data   = array();
			$search = $html->find( 'select[name="eventsType"] option' );
			$i      = 0;

			foreach( $search as $option ) {
				if ( $i !== 0 ) {
					if ( '1' === $option->getAttribute( 'tag' ) ) {
						$name = str_replace( '[ ', '', $option->plaintext );
						$name = str_replace( ' ]', '', $name );

						$filename = strtolower( str_replace( ' ', '-', $name ) );

						$data['EventGroupID'][ $name ] = array(
							'name'     => $name,
							'filename' => $filename . '-' . $option->getAttribute( 'value' ),
							'id'       => $option->getAttribute( 'value' ),
						);
					} else {

						$filename = str_replace( '(', '', $option->plaintext );
						$filename = str_replace( ')', '', $filename );
						$filename = str_replace( '&#39;', '-', $filename );
						$filename = strtolower( str_replace( ' ', '-', $filename ) );

						$data['events'][ $option->plaintext ] = array(
							'name'     => $option->plaintext,
							'filename' => $filename . '-' . $option->getAttribute( 'value' ),
							'id'       => $option->getAttribute( 'value' )
						);
					}
				}
				$i++;
			}

			$convert = new Convert();

			try {

				$convert->save_json(
					$convert->array_to_json( $data['EventGroupID'] ),
					'event-group-ids.json',
					trailingslashit( 'data/sports/' . $types[ $id ]['handle'] )
				);


				$convert->save_json(
					$convert->array_to_json( $data['events'] ),
					'events.json',
					trailingslashit( 'data/sports/' . $types[ $id ]['handle'] )
				);

			} catch ( \JsonException $e ) {
				$e->getMessage();
			}

			set_transient(
				'justodds_' . $types[ $id ]['handle'] . '_events',
				true,
				DAY_IN_SECONDS
			);
		}
	}
}