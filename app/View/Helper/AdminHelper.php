<?php

	App::uses('AppHelper', 'View/Helper');

	class CakeLogEntry {

		public function __construct($text) {
			$this->_text = $text;

			$lines = explode("\n", trim($text));
			$_firstLine = array_shift($lines);
			preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.*?): (.*)/',
				$_firstLine,
				$matches);
			$this->_time = $matches[1];
			$this->_type = $matches[2];
			$this->_message = $matches[3];
			$this->_detail = implode($lines, '<br>');
		}

		public function time() {
			return $this->_time;
		}

		public function type() {
			return $this->_type;
		}

		public function message() {
			return $this->_message;
		}

		public function details() {
			return $this->_detail;
		}

	}

	class AdminHelper extends AppHelper {

		public $helpers = [
			'TimeH'
		];

		public function formatCakeLog($errorLog) {
			preg_match_all('/(^\d\d\d\d-(.*?))(?=^\d\d\d\d-)/ms', $errorLog, $errors);
			if (empty($errors[1])) {
				return '<p>' . __('No log file found.') . '</p>';
			}

			$out = '';
			$k = 0;
			$errors[1] = array_reverse($errors[1]);
			foreach ($errors[1] as $error) {
				$e = new CakeLogEntry($error);
				$_i = self::tagId();
				$_details = $e->details();
				if (!empty($_details)) {
					$out .= '<button class="btn btn-mini" style="float:right;" onclick="$(\'#' . $_i . '\').toggle(); return false;">' . __('Details') . '</button>' . "\n";
				}
				$out .= '<pre style="font-size: 10px;">' . "\n";
				$out .= '<div class="row"><div class="span2" style="text-align: right">';
				$out .= $this->TimeH->formatTime($e->time(), 'eng');

				$out .= '</div>';
				$out .= '<div class="span7">';
				$out .= $e->message();
				if (!empty($_details)) {
					$out .= '<span id="' . $_i . '" style="display: none;">' . "\n";
					$out .= $_details;
					$out .= '</span>';
				}
				$out .= '</div></div>';
				$out .= '</pre>' . "\n";
				if ($k++ > 20) {
					break;
				}

			}
			return $out;
		}

	}
