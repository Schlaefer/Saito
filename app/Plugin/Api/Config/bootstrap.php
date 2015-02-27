<?php

	include CakePlugin::path('Api') . 'Lib' . DS . 'exceptions.php';

	$getUri = function () {
		if (!empty($_SERVER['PATH_INFO'])) {
			$uri = $_SERVER['PATH_INFO'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
				$uri = $_SERVER['REQUEST_URI'];
		} elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['SCRIPT_NAME'])) {
			$uri = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['PHP_SELF']);
		} elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
			$uri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif ($var = env('argv')) {
			$uri = $var[0];
		}
		return $uri;
	};

	if (strstr($getUri(), 'api/v1')) {
		Configure::write(
			'Exception',
			[
				'renderer' => 'Api.ApiExceptionRenderer'
			]
		);
	}

