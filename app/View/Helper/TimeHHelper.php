<?php

	App::uses('AppHelper', 'View/Helper');

	class TimeHHelper extends AppHelper {

		public $helpers = array(
				'Glasenuhr',
				'Time',
		);
		protected static $_timezoneGroups = array(
				'UTC' => DateTimeZone::UTC,
				'Africa' => DateTimeZone::AFRICA,
				'America' => DateTimeZone::AMERICA,
				'Antarctica' => DateTimeZone::ANTARCTICA,
				'Asia' => DateTimeZone::ASIA,
				'Atlantic' => DateTimeZone::ATLANTIC,
				'Europe' => DateTimeZone::EUROPE,
				'Indian' => DateTimeZone::INDIAN,
				'Pacific' => DateTimeZone::PACIFIC,
		);
		protected $now = false;
		protected $today = false;
		protected $_start = false;
		protected $_timeDiffToUtc = 0;

		protected $_timeAgoInWordsFuzzyLastEntry;
		protected $_timeAgoInWordsFuzzyLastItem;
		protected $_timeAgoInWordsFuzzyInterval;

		protected $_tAIWF_times = array();
		protected $_tAIWF_entries = array();

		public function beforeRender($viewFile) {
			parent::beforeRender($viewFile);
			$this->now = time();
			$this->today = mktime(0, 0, 0);

			// @td reimplement unsing Cake 2.2 CakeTime (?)
			$timezone_settings = Configure::read('Saito.Settings.timezone');
			if (empty($timezone_settings)) {
				$timezone_settings = 'UTC';
			}
			$timezone = new DateTimeZone($timezone_settings);
			$timeInTimezone = new DateTime('now', $timezone);
			$timeOnServer = new DateTime('now');
			$this->_timeDiffToUtc = $timeOnServer->getOffset() - $timeInTimezone->getOffset();
		}

		public function timezoneOptions() {
			$options = array( );

			$allTimeZonesValues = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

			foreach ( self::$_timezoneGroups as $groupTitle => $groupId ) :
				$timeZones = DateTimeZone::listIdentifiers($groupId);
				foreach ( $timeZones as $timeZoneTitle ) :
					$timezone = new DateTimeZone($timeZoneTitle);
					$timeInTimezone = new DateTime('now', $timezone);
					$timeDiffToUtc = $timeInTimezone->getOffset() / 3600;
					$options[$groupTitle][$timeZoneTitle] =
							$timeZoneTitle
							. ' (' . $timeInTimezone->format('H:m')
							. '; ' . $timeDiffToUtc . ')';
				endforeach;
			endforeach;

			return $options;
		}

		#@td user/admin time zone diff and admin format settings

		public function formatTime($timestamp, $format = 'normal', $custom = null) {
//		Stopwatch::start('formatTime');
			$timestamp = strtotime($timestamp) - $this->_timeDiffToUtc;

			if ( $format == 'normal' ) {
				$time_string = $this->_normal($timestamp);
			} elseif ( $format === 'short' ) {
				$time_string = $this->_short($timestamp);
			} elseif ( $format == 'custom' ) {
				$time_string = strftime($custom, $timestamp);
			} elseif ( $format == 'glasen' ) {
				$time_string = $this->_glasen($timestamp);
			}
//		Stopwatch::stop('formatTime');

			return $time_string;
		}

// end formatTime()

		protected function _normal($timestamp) {
			$time = '';
			if ( $timestamp > $this->today || $timestamp > ( $this->now - 21600 ) ) {
				// today or in the last 6 hours
				$time = strftime("%H:%M", $timestamp);
			} elseif ( $timestamp > ($this->today - 64800) ) {
				// yesterday but in the last 18 hours
				$time = __('yesterday') . ' ' . strftime("%H:%M", $timestamp);
			} else {
				// yesterday and 18 hours and older
				$time = strftime("%d.%m.%Y %H:%M", $timestamp);
			}

			$time = '<span title="' . strftime("%d.%m.%Y %T", $timestamp) . '">' . $time . '</span>';
			return $time;
		}

		protected function _short($timestamp) {
			$time = date("d.m.", $timestamp);
			return $time;
		}

		protected function _glasen($timestamp) {
			if ( $timestamp > $this->today || $timestamp > ( $this->now - 21600 ) ) {
				$time = $this->Glasenuhr->ftime($timestamp);
			} elseif ( $timestamp > $this->today - 64800 ) {
				$time = __('yesterday') . ' ' . $this->Glasenuhr->ftime($timestamp);
			} else {
				$time = strftime("%d.%m.%Y", $timestamp) . ' ' . $this->Glasenuhr->ftime($timestamp);
			}
			return $time;
		}

		/**
		 * @param $time datetime string
		 * @param array $options
		 * @return bool|string false or formated time string
		 */
		public function timeAgoInWordsFuzzy($time, $options = array()) {
				$defaults = array(
					'conversationCoolOff' => 300,
				);
				$options += $defaults;

				$time_unix = strtotime($time);

				$out = false;

				if (empty($this->_tAIWF_times)) {
					$this->_tAIWF_times[] = $time_unix;
					return $out;
				}

				$last_time = end($this->_tAIWF_times);
				$this->_tAIWF_times[] = $time_unix;

				if ($time_unix > $last_time - $options['conversationCoolOff']) {
					return $out;
				}

				$this->_tAIWF_entries[] = $time_unix;
				$out = $this->formatTime(gmdate('Y-m-d H:i:s', $last_time));

				return $out;

			}

		public function timeAgoInWordsFuzzyGetLastTime() {
			return $this->formatTime(gmdate('Y-m-d H:i:s', end($this->_tAIWF_times)));
		}
	}

?>