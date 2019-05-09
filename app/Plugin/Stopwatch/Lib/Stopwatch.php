<?php

class Stopwatch {

	protected static $_startupTime = 0;

	protected static $_instance = null;

	protected static $_enableTimer = false;

	protected static $_wallStart = 0;

	protected static $_userStart = 0;

	protected static $_wallLast = 0;

	protected static $_userLast = 0;

	protected static $_events;

	protected static $_sums = array();

	protected static $_starts = array();

	protected static $_stopwatchTime = 0;

	protected static $_stopwatchCalls = 0;

	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new Stopwatch();
		}
		return self::$_instance;
	}

	protected function __construct() {
	}

	private function __clone() {
	}

	static public function reset() {
		self::$_startupTime = 0;
		self::$_instance = null;
		self::$_enableTimer = false;
		self::$_wallStart = 0;
		self::$_userStart = 0;
		self::$_wallLast = 0;
		self::$_userLast = 0;
		self::$_events = [];
		self::$_sums = array();
		self::$_starts = array();
		self::$_stopwatchTime = 0;
		self::$_stopwatchCalls = 0;
	}

	static protected function _addEvent($x, $event = null) {
		if (self::$_enableTimer === false) {
			return;
		}

		list($usec, $sec) = explode(' ', microtime());
		$wtime = ($sec + $usec);
		if (!self::$_wallStart) {
			self::$_wallStart = $wtime;
		}

		$dat = @getrusage();
		if ($dat === null) {
			// some hosters disable getrusage() while hardening their PHP
			$utime = 0;
		} else {
			$utime = ($dat['ru_utime.tv_sec'] + $dat['ru_utime.tv_usec'] / 1000000);
		}

		if (!self::$_userStart) {
			self::$_userStart = $utime;
		}

		$udiff = ($wtime - self::$_wallStart == 0) ? 0 : $utime - self::$_userLast;
		self::$_userLast = $utime;

		$wdiff = ($wtime - self::$_wallStart == 0) ? 0 : $wtime - self::$_wallLast;
		self::$_wallLast = $wtime;

		if (!isset(self::$_starts[$x])) {
			self::$_starts[$x]['wtime'] = $wtime;
			self::$_starts[$x]['utime'] = $utime;
		} else {
			if (!isset(self::$_sums[$x]['wtime'])) {
				self::$_sums[$x]['wtime'] = 0;
				self::$_sums[$x]['utime'] = 0;
				self::$_sums[$x]['times'] = 0;
			}
			self::$_sums[$x]['wtime'] = self::$_sums[$x]['wtime'] + $wtime - self::$_starts[$x]['wtime'];
			self::$_sums[$x]['utime'] = self::$_sums[$x]['utime'] + $utime - self::$_starts[$x]['utime'];
			self::$_sums[$x]['times'] = self::$_sums[$x]['times'] + 1;
			unset(self::$_starts[$x]);
		}

		switch ($event) {
			case 'start':
				$x = '* ' . $x;
				break;
			case 'stop':
				$x = '† ' . $x;
				break;
		}

		self::$_events[] = array(
			'title' => $x,
			'wtime' => $wtime - self::$_wallStart,
			'utime' => $utime - self::$_userStart,
			'wdiff' => $wdiff,
			'udiff' => $udiff,
			'mem' => memory_get_usage()
		);

		// endtime
		list($eusec, $esec) = explode(' ', microtime());
		$ewtime = ($esec + $eusec);
		self::$_stopwatchTime += ($ewtime - $wtime);
		self::$_stopwatchCalls++;
	}

	static protected function _timeToCake() {
		return TIME_START - $_SERVER['REQUEST_TIME_FLOAT'];
	}

	static protected function _timeFromCakeToStopwatch() {
		return self::$_startupTime - TIME_START;
	}

	static protected function _timeToStopwatch() {
		return self::$_startupTime - $_SERVER['REQUEST_TIME_FLOAT'];
	}

	public static function getString() {
		if (self::$_enableTimer === false) {
			return;
		}

		self::start('now');

		$out = "";
		$out .= 'Time to Cake: ' . sprintf('%05.3f', self::_timeToCake()) . " s\n";
		$out .= 'Cake bootstrap: ' . sprintf('%05.3f', self::_timeFromCakeToStopwatch()) . " s\n";

		$out .= "W\tU\tW_delta\tU_delta\tMem [MB]\n";
		$_seriesIndex = 1;
		foreach (self::$_events as $k => $v) {
			$out .= '<span id="stopwatch-' . $_seriesIndex++ . '" class="stopwatch-row">';
			$out .= sprintf("%05.3f\t%05.3f\t%05.3f\t%05.3f\t%5.1f\t%s\n",
				$v['wtime'],
				$v['utime'],
				$v['wdiff'],
				$v['udiff'],
				$v['mem'] / 1048576,
				$v['title']);
			$out .= '</span>';
		}

		$out .= "\n\n";

		for ($i = 0; $i < 100; $i++) {
			Stopwatch::start('e');
			Stopwatch::stop('e');
		}
		$_e = array_pop(self::$_sums);
		$_eW = $_e['wtime'] / 100;
		$_eU = $_e['utime'] / 100;

		self::$_events = array_slice(self::$_events, 0, -200);

		$out .= "W_sum\tU_sum\tW_%\tU_%\t#\tW_ø\n";

		$_lastTimestamp = end(self::$_events);
		$wlast = $_lastTimestamp['wtime'] / 100;
		$ulast = $_lastTimestamp['utime'] / 100;
		foreach (self::$_sums as $k => $v) {
			// on vagrant $ulast may be 0 for unknown reason when running test cases
			// ugly hack to suppress output in test-cases, where it isn't read anyway
			if (empty($ulast)) {
				break;
			}
			$v['wtime'] = $v['wtime'] - ($_eW * $v['times']);
			$v['utime'] = $v['utime'] - ($_eW * $v['times']);

			$out .= sprintf("%05.3f\t%05.3f\t%04.1f\t%04.1f\t%u\t%05.3f\t%s\n",
				$v['wtime'],
				$v['utime'],
					$v['wtime'] / $wlast,
					$v['utime'] / $ulast,
				$v['times'],
					$v['wtime'] / $v['times'],
				$k);
		}

		$out .= "\n\n" . self::printStatistic();

		return $out;
	}

	public static function getJs() {
		if (self::$_enableTimer === false) {
			return;
		}
		$data = array();
		foreach (self::$_events as $v) {
			$data[] = array(
				'label' => $v['title'],
				'data' => [[1, $v['wdiff']], [2, $v['udiff']]]
			);
		}
		$out = json_encode($data);
		return $out;
	}

	public static function init() {
			self::reset();
			self::$_startupTime = microtime(true);
	}

	public static function enable() {
		self::$_enableTimer = true;
	}

	public static function disable() {
		self::$_enableTimer = false;
	}

	public static function start($text) {
		self::_addEvent($text, 'start');
	}

	public static function stop($text) {
		self::_addEvent($text, 'stop');
	}

	public static function printStatistic() {
		return self::$_stopwatchCalls . " calls with ca " . sprintf("%05.3f", self::$_stopwatchTime) . ' sec overhead.';
	}

	public static function getWallTime($divider = null) {
		$thousand = '';

		if ($divider === 'eng') {
			$divider = '.';
		}

		if (strlen($divider) < 2) {
			$decimal = $divider;
		} else {
			$decimal = ',';
		}

		self::start('getWallTime()');
		self::end('getWallTime()');
		$time = self::$_events[count(self::$_events) - 1]['wtime'] +
			self::_timeToStopwatch();
		return number_format( $time, 3, $decimal, $thousand);
	}

/**
 * Alias for self::stop
 *
 * @param type $text
 */
	public static function end($text) {
		self::stop($text);
	}

}
