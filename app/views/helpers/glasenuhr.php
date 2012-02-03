<?php
class GlasenuhrHelper extends AppHelper {

	/**
	 * Array with name strings of the watches
	 *
	 * @var array
	 */
	protected static $_watchNames = null;

	/**
	 * Sets if the class has initialized. True after $this->setup() has run.
	 *
	 * @var bool
	 */
	protected $_start = false;

	/**
	 * Setup with settings
	 *
	 * @param array $settings
	 */
	public function setup($settings = array()) {
		$this->_start = true;
		$defaults = array(
				'watches' => array (
						0 => __('Nachts', true),
						1 => __('Morgens', true),
						2 => __('Vormittags', true),
						3 => __('Nachmittags', true),
						4 => __('Freiwache', true),
						5 => __('Abends', true),
					),
		);

		$options = array_merge($defaults, $settings);
		extract($options);

		self::$_watchNames = $watches;
	} // end setup()

	/**
	 * Returns formated time
	 *
	 * @param int $timestamp
	 * @return string
	 */
	public function ftime($timestamp) {
		$glasen = $this->glasen($timestamp);
		$watch	= $this->watchName($timestamp);
		
		return sprintf("%s Gl. %s", $glasen, $watch);
	} //end ftime()

	/**
	 * Calculates bells
	 *
	 * @param int $timestamp
	 * @return int
	 */
	public function glasen($timestamp) {
		$hour = $this->_decimalHourFromTimestamp($timestamp);

		// Normierung auf halbe Stunden: 60 min/30 min = 2
		$glasen = ($hour * 2 ) % 8;
		if ($glasen == 0) $glasen = $glasen + 8;
		return $glasen;
	}

	/**
	 * Calculates watch
	 *
	 * @param int timestamp
	 * @return string
	 */
	public function watchName($timestamp) {
		if (!$this->_start) $this->setup();
	
		// 30 * 60 s = 1800 s for half an hour shift:
		// first half hour of new watch is 8 bells on the prior watch
		$hour = $this->_decimalHourFromTimestamp($timestamp - 1800);
		return (self::$_watchNames[(int)$hour/4]);

	} // end _watchName()

	/**
	 * Returns the hour with the minutes as decimal value
	 *
	 * Example: `01:20` returns `1.333…`; `04:45` returns `4.75`
	 *
	 * @param int $timestamp
	 * @return float
	 */
	protected function _decimalHourFromTimestamp($timestamp) {
		$getdate = getdate($timestamp);
		return (int)$getdate['hours'] + ((int)$getdate['minutes'] / 60);
	} // end decimalHourFromTimestamp()
}
?>