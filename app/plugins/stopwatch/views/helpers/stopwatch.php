<?php

App::import('Lib', 'Stopwatch.Stopwatch');

class StopwatchHelper extends Helper {

	public function getResult() {
		return "<pre>" . Stopwatch::getString() . "</pre>";
		}
	}

?>