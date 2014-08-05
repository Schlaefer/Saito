<?php

	interface SaitoCacheEngineInterface {

		public function read($name);

		public function write($name, $content);

	}