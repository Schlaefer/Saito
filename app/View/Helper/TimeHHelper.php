<?php
	App::uses('AppHelper', 'View/Helper');

class TimeHHelper extends AppHelper {
	public $helpers = array(
			'Glasenuhr',
	);

	protected $now 		= false;
	protected $today 	= false;

	protected $_start = false;

	public function beforeRender($viewFile) {
		parent::beforeRender($viewFile);
		$this->now = time();
		$this->today = mktime(0, 0, 0);
	}

	#@td user/admin time zone diff and admin format settings
	public function formatTime($timestamp, $format = 'normal') {
//		Stopwatch::start('formatTime');
		$timestamp = strtotime($timestamp);

		if($format == 'normal') {
			$time_string = $this->_normal($timestamp);
		} elseif ( $format === 'short' ) {
			$time_string = $this->_short($timestamp);
		} elseif ($format == 'glasen') {
			$time_string = $this->_glasen($timestamp);
		}
//		Stopwatch::stop('formatTime');

		return $time_string;
	} // end formatTime()

	protected function _normal($timestamp) {
		$time = '';
		if ($timestamp > $this->today || $timestamp > ( $this->now - 21600 )) {
		// today or in the last 6 hours
			$time = strftime("%H:%M", $timestamp);
		} elseif ($timestamp > ($this->today - 64800)) {
		// yesterday but in the last 18 hours
			$time = __('yesterday') . ' ' . $time = strftime("%H:%M", $timestamp);
		} else {
		// yesterday and 18 hours and older
			$time = strftime("%d.%m.%Y %H:%M", $timestamp);
		}
		return $time;

	}

	protected function _short($timestamp) {
		$time = date("d.m.", $timestamp);
		return $time;
	}

	protected function _glasen($timestamp) {
		if ($timestamp > $this->today || $timestamp > ( $this->now - 21600 )) {
			$time = $this->Glasenuhr->ftime($timestamp);
		} elseif ($timestamp > $this->today - 64800) {
			$time = __('yesterday') . ' ' . $this->Glasenuhr->ftime($timestamp);
		} else {
			$time = strftime("%d.%m.%Y", $timestamp) . ' ' . $this->Glasenuhr->ftime($timestamp);
		}
		return $time;
	}

}
?>