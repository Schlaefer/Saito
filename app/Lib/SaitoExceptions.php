<?php

	namespace Saito;

	\App::uses('Saito\Logger\ForbiddenLogger', 'Lib');

	class ForbiddenException extends \HttpException {

		protected $__Logger;

		/**
		 * @throws \InvalidArgumentException
		 */
		public function __construct($message = null, $data = []) {
			$this->__Logger = new \Saito\Logger\ForbiddenLogger();
			$this->__Logger->write($message, $data);
			parent::__construct($message, 403);
		}

	}

	class BlackHoledException extends \BadRequestException {

		public function __construct($type = null, $data = []) {
			$message = 'Request was blackholed. Type: ' . $type;
			$this->__Logger = new \Saito\Logger\ExceptionLogger();
			$this->__Logger->write($message, $data);
			parent::__construct($message, 400);
		}

	}

