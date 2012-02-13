<?php

App::uses('AppHelper', 'View/Helper');
App::import('Lib', 'Stopwatch.Stopwatch');

class StopwatchHelper extends AppHelper {

	public function getResult() {
		return "<pre>" . Stopwatch::getString() . "</pre>";
		}
	}

?>