<?php

	namespace Saito;

	\App::uses('Saito\ForbiddenLogger', 'Lib');

	class ForbiddenException extends \HttpException {

		private $__Logger;

		/**
		 * @throws \InvalidArgumentException
		 */
		public function __construct($message = null, $data = []) {
			$this->__Logger = new \Saito\ForbiddenLogger();
			$this->__Logger->write($message, $data);
			parent::__construct($message, 403);
		}

	}

