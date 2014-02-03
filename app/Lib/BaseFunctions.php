<?php

	/**
	 * Sets variable to value if it's undefined
	 *
	 * @param $variable
	 * @param $value
	 */
	function SDV(&$variable, $value) {
		if (!isset($variable)) {
			$variable = $value;
		}
	}
