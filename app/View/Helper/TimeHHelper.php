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

		protected $_now = false;

		protected $_today = false;

		protected $_start = false;

		protected $_timeDiffToUtc = 0;

		public function beforeRender($viewFile) {
			parent::beforeRender($viewFile);
			$this->_now = time();
			$this->_today = mktime(0, 0, 0);

			// @td reimplement unsing Cake 2.2 CakeTime (?)
			$_timezoneSettings = Configure::read('Saito.Settings.timezone');
			if (empty($_timezoneSettings)) {
				$_timezoneSettings = 'UTC';
			}
			$timezone = new DateTimeZone($_timezoneSettings);
			$timeInTimezone = new DateTime('now', $timezone);
			$timeOnServer = new DateTime('now');
			$this->_timeDiffToUtc = $timeOnServer->getOffset() - $timeInTimezone->getOffset();
		}

		public function timezoneOptions() {
			$options = array( );

			$allTimeZonesValues = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

			foreach (self::$_timezoneGroups as $groupTitle => $groupId) :
				$timeZones = DateTimeZone::listIdentifiers($groupId);
				foreach ($timeZones as $timeZoneTitle) :
					$timezone = new DateTimeZone($timeZoneTitle);
					$timeInTimezone = new DateTime('now', $timezone);
					$timeDiffToUtc = $timeInTimezone->getOffset() / 3600;
					$options[$groupTitle][$timeZoneTitle] =
							$timeZoneTitle .
							' (' . $timeInTimezone->format('H:m') .
							'; ' . $timeDiffToUtc . ')';
				endforeach;
			endforeach;

			return $options;
		}

/**
 *
 *
 * #@td user/admin time zone diff and admin format settings
 *
 * @param        $timestamp
 * @param string $format
 * @param null   $custom
 *
 * @return bool|string
 */
		public function formatTime($timestamp, $format = 'normal', $custom = null) {
			// Stopwatch::start('formatTime');
			$timestamp = strtotime($timestamp) - $this->_timeDiffToUtc;

			if ($format == 'normal') {
				$_timeString = $this->_normal($timestamp);
			} elseif ($format === 'short') {
				$_timeString = $this->_short($timestamp);
			} elseif ($format == 'custom') {
				$_timeString = strftime($custom, $timestamp);
			} elseif ($format == 'eng') {
				$_timeString = strftime('%F %T', $timestamp);
			} elseif ($format == 'glasen') {
				$_timeString = $this->_glasen($timestamp);
			}

			// Stopwatch::stop('formatTime');
			return $_timeString;
		}

		protected function _normal($timestamp) {
			$time = '';
			if ($timestamp > $this->_today || $timestamp > ($this->_now - 21600)) {
				// today or in the last 6 hours
				$time = strftime("%H:%M", $timestamp);
			} elseif ($timestamp > ($this->_today - 64800)) {
				// yesterday but in the last 18 hours
				$time = __('yesterday') . ' ' . strftime("%H:%M", $timestamp);
			} else {
				// yesterday and 18 hours and older
				$time = strftime("%d.%m.%Y", $timestamp);
			}

			$time = '<span title="' . strftime("%d.%m.%Y %T", $timestamp) . '">' .
					$time . '</span>';
			return $time;
		}

		protected function _short($timestamp) {
			$time = date("d.m.", $timestamp);
			return $time;
		}

		protected function _glasen($timestamp) {
			if ( $timestamp > $this->_today || $timestamp > ( $this->_now - 21600 ) ) {
				$time = $this->Glasenuhr->ftime($timestamp);
			} elseif ( $timestamp > $this->_today - 64800 ) {
				$time = __('yesterday') . ' ' . $this->Glasenuhr->ftime($timestamp);
			} else {
				$time = strftime("%d.%m.%Y", $timestamp) . ' ' . $this->Glasenuhr->ftime($timestamp);
			}
			return $time;
		}

		public function mysqlTimestampToIso($date) {
			$unixTimeStamp = strtotime($date);
			if ($unixTimeStamp < 0) {
				$unixTimeStamp = 0;
			}
			return date('c', $unixTimeStamp);
		}

	}
