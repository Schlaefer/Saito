<?php

	namespace App\Lib\Fallback;

	class Locale extends \Symfony\Component\Intl\Locale\Locale {

		public static function parseLocale($locale) {
			return ['language' => 'en', 'region' => null];
		}

	}