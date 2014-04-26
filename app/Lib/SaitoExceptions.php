<?php

	namespace Saito;

	\App::uses('Saito\Logger\ForbiddenLogger', 'Lib');

	class ForbiddenException extends \HttpException {

		private $__Logger;

		/**
		 * @throws \InvalidArgumentException
		 */
		public function __construct($message = null, $data = []) {
			$this->__Logger = new \Saito\Logger\ForbiddenLogger();
			$this->__Logger->write($message, $data);
			parent::__construct($message, 403);
		}

	}

