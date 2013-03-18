<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Stopwatch {
	protected static $instance = NULL;

	protected static $_enableTimer = FALSE;

	protected static $_wallStart = 0;
	protected static $_userStart = 0;
	protected static $_wallLast	 = 0;
	protected static $_userLast	 = 0;

	protected static $_events;
	protected static $_sums = array();
	protected static $_starts = array();

	protected static $_stopwatchTime = 0;
	protected static $_stopwatchCalls = 0;

	public static function getInstance() {
		if ( self::$instance === NULL ) {
			self::$instance = new Stopwatch();
		}
		return self::$instance;
		}

	protected function __construct() { ; }
	private function __clone() { ; }

	static protected function _addEvent($x, $event = null) {
	 	if ( self::$_enableTimer === FALSE ) { return; }

		list($usec,$sec) = explode(' ',microtime());
		$wtime = ($sec+$usec);
		if (!self::$_wallStart) self::$_wallStart = $wtime;
		$dat = getrusage();
		$utime = ($dat['ru_utime.tv_sec']+$dat['ru_utime.tv_usec']/1000000);
		if (!self::$_userStart) self::$_userStart=$utime;

		$udiff = ($wtime-self::$_wallStart == 0) ? 0 : $utime-self::$_userLast;
		self::$_userLast = $utime;

		$wdiff = ($wtime-self::$_wallStart == 0) ? 0 : $wtime-self::$_wallLast;
		self::$_wallLast = $wtime;

		if (!isset(self::$_starts[$x])) {
			self::$_starts[$x]['wtime'] = $wtime;
			self::$_starts[$x]['utime'] = $utime;
		} else {
			if(!isset(self::$_sums[$x]['wtime'])) {
				self::$_sums[$x]['wtime'] = 0;
				self::$_sums[$x]['utime'] = 0;
				self::$_sums[$x]['times'] = 0;
				}
			self::$_sums[$x]['wtime'] = self::$_sums[$x]['wtime'] + $wtime - self::$_starts[$x]['wtime'];
			self::$_sums[$x]['utime'] = self::$_sums[$x]['utime'] + $utime	- self::$_starts[$x]['utime'];
			self::$_sums[$x]['times'] = self::$_sums[$x]['times'] + 1;
			unset(self::$_starts[$x]);
			}

		switch($event) {
			case 'start':
				$x = '* '. $x;
				break;
			case 'stop':
				$x = '† '. $x;
				break;
			}

		self::$_events[] = array (
				'title' => $x,
				'wtime' => $wtime-self::$_wallStart,
				'utime' => $utime-self::$_userStart,
				'wdiff' => $wdiff,
				'udiff' => $udiff,
				'mem'	=> memory_get_usage()
			);

			// endtime
			list($eusec,$esec) = explode(' ',microtime());
			$ewtime = ($esec+$eusec);
			self::$_stopwatchTime += ($ewtime - $wtime);
			self::$_stopwatchCalls++;
		}

	public static function getString() {
		if ( self::$_enableTimer === FALSE ) return;

		self::start('now');

		$out = "";

		$out .= "W\tU\tW_delta\tU_delta\tMem [MB]\n";
		$series_index = 1;
		foreach(self::$_events as $k => $v) {
			$out .= '<span id="stopwatch-' . $series_index++ . '" class="stopwatch-row">';
			$out .= sprintf("%05.3f\t%05.3f\t%05.3f\t%05.3f\t%5.1f\t%s\n", $v['wtime'], $v['utime'], $v['wdiff'], $v['udiff'], $v['mem']/1048576, $v['title']);
			$out .= '</span>';
		}

		$out .= "\n\n";

		for($i=0; $i<100; $i++) {
				Stopwatch::start('e');
				Stopwatch::stop('e');
		}
		$e = array_pop(self::$_sums);
		$e_w = $e['wtime'] / 100;
		$e_u = $e['utime'] / 100;

		self::$_events = array_slice(self::$_events, 0, -200);

		$out .= "W_sum\tU_sum\tW_%\tU_%\t#\tW_ø\n";

		$last_timestamp = end(self::$_events);
		$wlast = $last_timestamp['wtime'] / 100;
		$ulast = $last_timestamp['utime'] / 100;
		foreach (self::$_sums as $k => $v) {
				$v['wtime'] = $v['wtime']-($e_w * $v['times']);
				$v['utime'] = $v['utime']-($e_w * $v['times']);

			$out .= sprintf("%05.3f\t%05.3f\t%04.1f\t%04.1f\t%u\t%05.3f\t%s\n",
					$v['wtime'],
					$v['utime'],
					$v['wtime']/$wlast,
					$v['utime']/$ulast,
					$v['times'],
					$v['wtime']/$v['times'], $k);
		}

		$out .=  "\n\n" . self::printStatistic();

		return $out;
	}

	public static function getJs() {
		if ( self::$_enableTimer === FALSE ) return;
		$data = array();
		foreach(self::$_events as $k => $v) {
			$data[] = array(
			    'label' => $v['title'],
					'data' => array(
							array(
									1,
									$v['wdiff'],
							),
							array(
									2,
									$v['udiff'],
							),
					)
			);
		}
		$out = json_encode($data);
		return $out;
	}

	public static function enable() {
		self::$_enableTimer = TRUE;
		}

	public static function disable() {
		self::$_enableTimer = FALSE;
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

  public static function getWallTime() {
		self::start('getWallTime()');
		self::end('getWallTime()');
    return sprintf("%05.3f", self::$_events[count(self::$_events) - 1]['wtime']);
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
?>