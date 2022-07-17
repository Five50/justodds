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

use DateTime;

if ( ! defined( 'WPINC' ) ) {
	die( 'Restricted Access' );
}

class ShortCodes {

	/**
	 * Magic method contruct
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_shortcode( 'justodds', array( $this, 'get_shortcode_event_odds' ) );
		add_shortcode( 'justodds-tip', array( $this, 'get_shortcode_event_tip' ) );
	}

	public function get_shortcode_event_tip( $atts ) {

		$args = shortcode_atts(
			[
				'id'      => 2,
				'event'   => 'grand-national',
				'tip'     => ''
			],
			$atts
		);

		return sprintf(
			'<tr>%s</tr>',
			'testing'
		);
	}

	/**
	 * Get shortcode justodds
	 *
	 * @since 0.1.0
	 *
	 * @access public
	 * @param $atts shortcode attributes.
	 * @return string|void
	 * @throws \Exception
	 */
	public function get_shortcode_event_odds( $atts ) {

		$args = shortcode_atts(
			[
				'id'      => 2,
				'event'   => 'grand-national',
				'details' => 'true',
				'group'   => 'false',
			],
			$atts
		);

		/**
		 * Create fetch object.
		 *
		 * @since 0.1.0
		 */
		$fetch = new Fetch();
		$types = $fetch->get_types();

		$folder   = ( 'true' === $args['group'] ) ? '/events-group' : '/events';
		$location = dirname( __DIR__ ) . '/data/sports/' . $types[ $args['id'] ]['handle'] . $folder;

		if ( file_exists( $location ) ) {
			$files = scandir( $location, SCANDIR_SORT_ASCENDING );

			if ( $files ) {
				$results = array();
				$output  = '';

				foreach( $files as $file ) {
					$event = strtolower( str_replace( ' ', '-', $args['event'] ) );

					if ( strpos( $file, $event . '-' ) !== false ) {
						$results[] = $file;
					}
				}

				if ( ! empty( $results ) ) {
					$json = $fetch->get_json(
						$results[0],
						'/data/sports/' . $types[ $args['id'] ]['handle'] . $folder
					);

					if ( $json ) {
						$output .= '<div class="justodds">';
						$code = '365_878972';

						if ( 'true' === $args['group'] ) {
							foreach( $json['Event'] as $ev ) {

								$participants = array();

								$fid = $ev['Market'][0]['@attributes']['FID'];

								$loop = is_array( $ev['Market'] ) ?
									$ev['Market'][0]['Participant'] :
									$ev['Market']['Participant'];

								if ( null === $loop ) {
									return sprintf(
										'<p>%s</p>',
										esc_html__('Unable to find data for event', 'justodds' )
									);
								}

								foreach( $loop as $participant ) {
										$name    = $participant['@attributes']['Name'];
										$id      = $participant['@attributes']['ID'];
										$avg     = $participant['@attributes']['AVG'];
										$updated = $participant['@attributes']['LastUpdated'];
										$odds    = $participant['@attributes']['Odds'];
										$saddle  = $participant['@attributes']['SaddleCloth'];

										$participants[ $participant['@attributes']['Name'] ] = sprintf(
											'<tr class="justodds-event-participant">
											<td class="justodds-event-participant__details">
												%s
												<span class="justodds-event-participant__details-name">%s</span>
											</td>
											<td class="justodds-event-participant__odds">
												<a class="justodds-event-participant-link" href="%s" data-updated="%s">%s</a>
											</td>
										</tr>',
											! empty( $saddle ) ? sprintf(
												'<span class="justodds-event-participant__details-saddle">%s</span>',
												$saddle
											) : '',
											$name,
											'https://www.bet365.com/betslip/instantbet/default.aspx?fid=' . $fid . '&participantid=' . $id .  '&affiliatecode=' . $code . '&odds=' . $odds . '&Instantbet=1&AVG=' . $avg,
											$updated,
											$odds
										);
									}

								$offtime    = $ev['@attributes']['OffTime'];
								$bits = explode( '/', $offtime );
								$offtime = $bits[1] . '/' . $bits[0] . '/' . $bits[2];

								$datetime   =  new \DateTime( $offtime );
								$event_date =  $datetime->format('l jS \of F Y');
								$event_time = $datetime->format('H:i' );
								$event_name = str_replace( $datetime->format('g.i' ), '', $ev['@attributes']['Name'] );

								if ( 'true' === $args['details'] ) {

										$info = array();

										$info['count']    = ( count( $participants ) > 1 ) ? count( $participants ) .  esc_html__( ' Runners', 'justodds' ) : null;
										$info['comment']  = ! empty( $ev['@attributes']['EventComment'] ) ? $ev['@attributes']['EventComment'] : null;
										$info['type']     = ! empty( $ev['@attributes']['RaceType'] ) ?  $ev['@attributes']['RaceType'] : null;
										$info['distance'] = ! empty( $ev['@attributes']['RaceDistance'] ) ? $ev['@attributes']['RaceDistance'] : null;

										$details = sprintf(
											'<ul class="justodds-details">%s %s %s</ul>',
											! empty( $event_date ) ? sprintf(
												'<li>%s %s</li>',
												esc_html__('Date:', 'justodds' ),
												$event_date
											) : '',
											! empty( $event_time ) ? sprintf(
												'<li>%s %s</li>',
												esc_html__('Time:', 'justodds' ),
												$event_time
											) : '',
											sprintf(
												'<li>%s %s</li>',
												esc_html__('Details:', 'justodds' ),
												implode( ', ', $info )
											)
										);
									}

								$output .= sprintf(
										'<div class="justodds-event">
										<div class="justodds-event__details"><span>%s</span>%s</div> 
										<table class="justodds-event__participants">
											<thead>
												<th></th>
												<th><div class="justodds-bet365">%s</div></th>
											</thead>
											<tbody>%s</tbody>
										</table>
									</div>',
										str_replace( 'BOG', '', $event_name ) . ' ' . $event_time,
										! empty( $details ) ? $details : '',
										'<svg width="64px" height="64px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><defs><style>.b{fill:#f9dc1c;}.c{fill:#027b5b;}.d{fill:#fafcfc;}</style></defs><polygon class="c" points="0 0 64 0 64 64 0 64 0 0"/><g><path class="b" d="M31.7,24.2c1.26-.5,2.64-.52,3.97-.5,1.21,.05,2.46,.43,3.38,1.29,1.22,1.13,1.27,3.45-.01,4.54-.33,.29-.71,.5-1.12,.64,.59,.22,1.17,.52,1.58,1.04,.65,.8,.75,1.95,.52,2.94-.21,.91-.85,1.64-1.63,2.06-1.4,.81-3.06,.79-4.61,.63-.73-.07-1.44-.23-2.14-.43-.03-.99,0-1.99-.01-2.98,1.21,.42,2.53,.71,3.79,.39,.84-.23,.89-1.62,.13-2-.8-.42-1.73-.26-2.58-.14,0-.94,0-1.87,0-2.81,.66,.03,1.32,.07,1.97-.04,.39-.07,.84-.28,.95-.73,.06-.36,.02-.77-.21-1.05-.27-.33-.69-.46-1.08-.49-.97-.05-1.95,.13-2.87,.49-.03-.95,0-1.89-.02-2.84h0Z"/><g><path class="d" d="M3.72,22.93c1.3,0,2.6,0,3.9,0-.01,1.81,0,3.63,0,5.44,.37-.56,.85-1.07,1.48-1.27,1.13-.38,2.5-.11,3.3,.85,.81,.96,1.09,2.29,1.12,3.55,.02,1.26-.1,2.59-.69,3.71-.4,.79-1.11,1.41-1.94,1.61-.83,.2-1.78,.17-2.51-.36-.5-.35-.77-.94-1.03-1.5,.02,.59-.01,1.17-.03,1.76H3.72c0-4.59,0-9.19,0-13.78h0Z"/><path class="d" d="M25.12,24.94c1.3-.44,2.59-.95,3.89-1.39-.01,1.2,0,2.41,0,3.61,.59,0,1.18,0,1.78,0,0,.91,0,1.82,0,2.73-.59,.01-1.19-.02-1.78,.01,.02,.95-.01,1.89,.01,2.84,.01,.42,.18,.9,.57,1.06,.4,.09,.81,0,1.2-.12,0,.91,0,1.83,0,2.74-1.09,.36-2.24,.61-3.38,.44-.69-.11-1.35-.5-1.73-1.13-.42-.68-.54-1.52-.57-2.31,0-1.18,0-2.36,0-3.53-.45,0-.91,0-1.36,0-.02-.91,0-1.83,0-2.74,.45,0,.91,0,1.37,0,0-.74-.02-1.48,.01-2.22h0Z"/><path class="b" d="M42.07,25.69c.91-1.19,2.31-1.87,3.74-1.97,1.33-.06,2.68-.04,3.97,.34-.02,.98,0,1.96-.01,2.94-1.05-.31-2.14-.53-3.24-.4-.66,.1-1.33,.46-1.66,1.09-.24,.43-.29,.92-.32,1.41,.79-.65,1.84-.81,2.81-.75,1.23,.08,2.4,.88,2.97,2.05,.42,.87,.5,1.89,.37,2.85-.14,1.02-.65,1.98-1.43,2.59-1.15,.95-2.69,1.23-4.11,1.04-1.24-.16-2.4-.86-3.17-1.9-.9-1.15-1.3-2.67-1.33-4.15-.04-1.8,.3-3.71,1.42-5.12h0Z"/><path class="b" d="M51.58,23.92c2.55,0,5.09,0,7.64,0,0,.98,0,1.97,0,2.95-1.36,.01-2.73-.02-4.09,.02,.01,.65,.02,1.3,0,1.96,.77-.02,1.57-.14,2.32,.14,1.07,.38,1.89,1.39,2.14,2.55,.24,1.12,.17,2.36-.38,3.36-.52,.94-1.47,1.52-2.44,1.79-1.75,.46-3.6,.21-5.32-.29-.03-.98,0-1.95-.01-2.93,1.1,.3,2.29,.58,3.41,.2,.93-.31,1.07-1.78,.34-2.38-.44-.35-1.01-.44-1.55-.46-.6,0-1.2,.07-1.79,.22-.12,.03-.23,.1-.33,.18,.03-.11,.05-.21,.06-.32,0-2.33,0-4.65,0-6.98h0Z"/><path class="d" d="M15.35,28.3c.99-1.06,2.46-1.37,3.82-1.36,1.03-.03,2.11,.24,2.88,1,.8,.78,1.17,1.94,1.32,3.05,.1,.68,.09,1.36,.09,2.04h-5.41c.03,.34,.09,.69,.34,.94,.43,.43,1.06,.49,1.62,.53,.99,.02,2.01-.1,2.89-.6,0,.82,0,1.65,0,2.47-1.1,.41-2.28,.53-3.44,.55-1.32,.03-2.73-.22-3.76-1.16-.96-.86-1.38-2.21-1.44-3.51-.09-1.39,.14-2.93,1.1-3.96h0Z"/><path class="c" d="M18.4,29.51c.31-.48,1.08-.51,1.39-.01,.28,.45,.3,1.01,.3,1.53-.68,0-1.36,0-2.03,0,.02-.52,.05-1.08,.34-1.52h0Z"/></g><path class="c" d="M8.27,29.81c.37-.13,.8,.07,.96,.44,.24,.5,.23,1.08,.24,1.63-.02,.55-.04,1.14-.34,1.62-.29,.47-1.04,.47-1.32-.01-.26-.45-.29-1-.29-1.51,0-.5,0-1,.14-1.48,.09-.31,.3-.61,.61-.68h0Z"/><path class="c" d="M45.36,30.83c.55-.21,1.25-.04,1.56,.52,.37,.68,.35,1.59-.05,2.25-.39,.7-1.45,.78-1.94,.16-.42-.51-.46-1.24-.33-1.87,.08-.44,.34-.89,.75-1.05h0Z"/></g></svg>',
										implode( '', $participants )
									);
							}
						} else {
							$participants = array();
							$fid          = $json['Event']['Market']['@attributes']['FID'];
							$total        = count( $json['Event']['Market'] );

							$loop = is_array( $json['Event']['Market'] ) && $total > 2 ?
								$json['Event']['Market'][0]['Participant'] :
								$json['Event']['Market']['Participant'];

							if ( null === $loop ) {
								return sprintf(
									'<p>%s</p>',
									esc_html__('Unable to find data for event', 'justodds' )
								);
							}

							foreach( $loop as $participant ) {
									$name    = $participant['@attributes']['Name'];
									$id      = $participant['@attributes']['ID'];
									$avg     = $participant['@attributes']['AVG'];
									$updated = $participant['@attributes']['LastUpdated'];
									$odds    = $participant['@attributes']['Odds'];
									$saddle  = $participant['@attributes']['SaddleCloth'];

									$participants[ $participant['@attributes']['Name'] ] = sprintf(
										'<tr class="justodds-event-participant">
									<td class="justodds-event-participant__details">
										%s
										<span class="justodds-event-participant__details-name">%s</span>
									</td>
									<td class="justodds-event-participant__odds">
										<a class="justodds-event-participant-link" href="%s" data-updated="%s">%s</a>
									</td>
								</tr>',
										! empty( $saddle ) ? sprintf(
											'<span class="justodds-event-participant__details-saddle">%s</span>',
											$saddle
										) : '',
										$name,
										'https://www.bet365.com/betslip/instantbet/default.aspx?fid=' . $fid . '&participantid=' . $id . '&affiliatecode=' . $code . '&odds=' . $odds . '&Instantbet=1&AVG=' . $avg,
										$updated,
										$odds
									);
								}

							if ( 'true' === $args['details'] ) {

									$info = array();

									$offtime = $json['Event']['@attributes']['OffTime'];
									$bits = explode( '/', $offtime );
									$offtime = $bits[1] . '/' . $bits[0] . '/' . $bits[2];


									$event_date = new DateTime( $offtime );
									$event_date = $event_date->format('l jS \of F Y');

									$event_time = new DateTime( $offtime );
									$event_time = $event_time->format('H:i' );

									$info['count']    = ( count( $participants ) > 1 ) ? count( $participants ) .  esc_html__( ' Runners', 'justodds' ) : null;
									$info['comment']  = ! empty( $json['Event']['@attributes']['EventComment'] ) ? $json['Event']['@attributes']['EventComment'] : null;
									$info['type']     = ! empty( $json['Event']['@attributes']['RaceType'] ) ?  $json['Event']['@attributes']['RaceType'] : null;
									$info['distance'] = ! empty( $json['Event']['@attributes']['RaceDistance'] ) ? $json['Event']['@attributes']['RaceDistance'] : null;

									$details = sprintf(
										'<ul class="justodds-details">%s %s %s</ul>',
										! empty( $event_date) ? sprintf(
											'<li>%s %s</li>',
											esc_html__('Date:', 'justodds' ),
											$event_date
										) : '',
										! empty( $event_time ) ? sprintf(
											'<li>%s %s</li>',
											esc_html__('Time:', 'justodds' ),
											$event_time
										) : '',
										sprintf(
											'<li>%s %s</li>',
											esc_html__('Details:', 'justodds' ),
											implode( ' ', $info )
										)
									);
								}

							$output .= sprintf(
									'<div class="justodds-event">
										<div class="justodds-event__details"><span>%s</span>%s</div> 
										<table class="justodds-event__participants">
											<thead>
												<th></th>
												<th><div class="justodds-bet365">%s</div></th>
											</thead>
											<tbody>%s</tbody>
										</table>
									</div>',
									$json['Event']['@attributes']['Name'],
									! empty( $details ) ? $details : '',
									'<svg width="64px" height="64px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><defs><style>.b{fill:#f9dc1c;}.c{fill:#027b5b;}.d{fill:#fafcfc;}</style></defs><polygon class="c" points="0 0 64 0 64 64 0 64 0 0"/><g><path class="b" d="M31.7,24.2c1.26-.5,2.64-.52,3.97-.5,1.21,.05,2.46,.43,3.38,1.29,1.22,1.13,1.27,3.45-.01,4.54-.33,.29-.71,.5-1.12,.64,.59,.22,1.17,.52,1.58,1.04,.65,.8,.75,1.95,.52,2.94-.21,.91-.85,1.64-1.63,2.06-1.4,.81-3.06,.79-4.61,.63-.73-.07-1.44-.23-2.14-.43-.03-.99,0-1.99-.01-2.98,1.21,.42,2.53,.71,3.79,.39,.84-.23,.89-1.62,.13-2-.8-.42-1.73-.26-2.58-.14,0-.94,0-1.87,0-2.81,.66,.03,1.32,.07,1.97-.04,.39-.07,.84-.28,.95-.73,.06-.36,.02-.77-.21-1.05-.27-.33-.69-.46-1.08-.49-.97-.05-1.95,.13-2.87,.49-.03-.95,0-1.89-.02-2.84h0Z"/><g><path class="d" d="M3.72,22.93c1.3,0,2.6,0,3.9,0-.01,1.81,0,3.63,0,5.44,.37-.56,.85-1.07,1.48-1.27,1.13-.38,2.5-.11,3.3,.85,.81,.96,1.09,2.29,1.12,3.55,.02,1.26-.1,2.59-.69,3.71-.4,.79-1.11,1.41-1.94,1.61-.83,.2-1.78,.17-2.51-.36-.5-.35-.77-.94-1.03-1.5,.02,.59-.01,1.17-.03,1.76H3.72c0-4.59,0-9.19,0-13.78h0Z"/><path class="d" d="M25.12,24.94c1.3-.44,2.59-.95,3.89-1.39-.01,1.2,0,2.41,0,3.61,.59,0,1.18,0,1.78,0,0,.91,0,1.82,0,2.73-.59,.01-1.19-.02-1.78,.01,.02,.95-.01,1.89,.01,2.84,.01,.42,.18,.9,.57,1.06,.4,.09,.81,0,1.2-.12,0,.91,0,1.83,0,2.74-1.09,.36-2.24,.61-3.38,.44-.69-.11-1.35-.5-1.73-1.13-.42-.68-.54-1.52-.57-2.31,0-1.18,0-2.36,0-3.53-.45,0-.91,0-1.36,0-.02-.91,0-1.83,0-2.74,.45,0,.91,0,1.37,0,0-.74-.02-1.48,.01-2.22h0Z"/><path class="b" d="M42.07,25.69c.91-1.19,2.31-1.87,3.74-1.97,1.33-.06,2.68-.04,3.97,.34-.02,.98,0,1.96-.01,2.94-1.05-.31-2.14-.53-3.24-.4-.66,.1-1.33,.46-1.66,1.09-.24,.43-.29,.92-.32,1.41,.79-.65,1.84-.81,2.81-.75,1.23,.08,2.4,.88,2.97,2.05,.42,.87,.5,1.89,.37,2.85-.14,1.02-.65,1.98-1.43,2.59-1.15,.95-2.69,1.23-4.11,1.04-1.24-.16-2.4-.86-3.17-1.9-.9-1.15-1.3-2.67-1.33-4.15-.04-1.8,.3-3.71,1.42-5.12h0Z"/><path class="b" d="M51.58,23.92c2.55,0,5.09,0,7.64,0,0,.98,0,1.97,0,2.95-1.36,.01-2.73-.02-4.09,.02,.01,.65,.02,1.3,0,1.96,.77-.02,1.57-.14,2.32,.14,1.07,.38,1.89,1.39,2.14,2.55,.24,1.12,.17,2.36-.38,3.36-.52,.94-1.47,1.52-2.44,1.79-1.75,.46-3.6,.21-5.32-.29-.03-.98,0-1.95-.01-2.93,1.1,.3,2.29,.58,3.41,.2,.93-.31,1.07-1.78,.34-2.38-.44-.35-1.01-.44-1.55-.46-.6,0-1.2,.07-1.79,.22-.12,.03-.23,.1-.33,.18,.03-.11,.05-.21,.06-.32,0-2.33,0-4.65,0-6.98h0Z"/><path class="d" d="M15.35,28.3c.99-1.06,2.46-1.37,3.82-1.36,1.03-.03,2.11,.24,2.88,1,.8,.78,1.17,1.94,1.32,3.05,.1,.68,.09,1.36,.09,2.04h-5.41c.03,.34,.09,.69,.34,.94,.43,.43,1.06,.49,1.62,.53,.99,.02,2.01-.1,2.89-.6,0,.82,0,1.65,0,2.47-1.1,.41-2.28,.53-3.44,.55-1.32,.03-2.73-.22-3.76-1.16-.96-.86-1.38-2.21-1.44-3.51-.09-1.39,.14-2.93,1.1-3.96h0Z"/><path class="c" d="M18.4,29.51c.31-.48,1.08-.51,1.39-.01,.28,.45,.3,1.01,.3,1.53-.68,0-1.36,0-2.03,0,.02-.52,.05-1.08,.34-1.52h0Z"/></g><path class="c" d="M8.27,29.81c.37-.13,.8,.07,.96,.44,.24,.5,.23,1.08,.24,1.63-.02,.55-.04,1.14-.34,1.62-.29,.47-1.04,.47-1.32-.01-.26-.45-.29-1-.29-1.51,0-.5,0-1,.14-1.48,.09-.31,.3-.61,.61-.68h0Z"/><path class="c" d="M45.36,30.83c.55-.21,1.25-.04,1.56,.52,.37,.68,.35,1.59-.05,2.25-.39,.7-1.45,.78-1.94,.16-.42-.51-.46-1.24-.33-1.87,.08-.44,.34-.89,.75-1.05h0Z"/></g></svg>',
									implode( '', $participants )
								);
						}

						$output .= '</div>';
					}
				}
				return $output;
			}
		}
	}
}