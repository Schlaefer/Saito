<?php

	App::uses('AppHelper', 'View/Helper');

	class TimeHHelper extends AppHelper {

		public $helpers = array(
			'Time'
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
		 * outputs a formatted time string
		 *
		 * #@td user/admin time zone diff and admin format settings
		 *
		 * @param $timestamp
		 * @param string $format
		 * @param array $options
		 * @return string
		 */
		public function formatTime($timestamp, $format = 'normal', array $options = []) {
			// Stopwatch::start('formatTime');
			$options += [
				'wrap' => true
			];

			$timestamp = strtotime($timestamp) - $this->_timeDiffToUtc;

			if ($format === 'normal' || empty($format)) {
				$_timeString = $this->_normal($timestamp);
			} elseif ($format === 'short') {
				$_timeString = date('d.m.', $timestamp);
			} elseif ($format === 'eng') {
				$_timeString = strftime('%F %T', $timestamp);
			} else {
				$_timeString = strftime($format, $timestamp);
			}

			if ($options['wrap']) {
				$attributes = [
					'datetime' => date(DATE_RFC3339, $timestamp),
					'title' => strftime("%F %T", $timestamp)
				];
				if (is_array($options['wrap'])) {
					$attributes += $options['wrap'];
				}
				foreach ($attributes as $attribute => $value) {
					$attributes[$attribute] = "$attribute=\"$value\"";
				}
				$attributes = implode(' ', $attributes);
				$_timeString = "<time $attributes>$_timeString</time>";
			}

			// Stopwatch::stop('formatTime');
			return $_timeString;
		}

		protected function _normal($timestamp) {
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

			return $time;
		}

		public function mysqlTimestampToIso($date) {
			if ($date === null) {
				return null;
			}
			$unixTimeStamp = strtotime($date);
			if ($unixTimeStamp < 0) {
				$unixTimeStamp = 0;
			}
			return date('c', $unixTimeStamp);
		}

	}
