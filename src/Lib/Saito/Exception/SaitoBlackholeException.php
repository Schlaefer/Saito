<?php

	namespace Saito\Exception;

	use Cake\Network\Exception\BadRequestException;
	use Saito\Exception\Logger\ExceptionLogger;

	class SaitoBlackholeException extends BadRequestException {

		public function __construct($type = null, $data = []) {
			$message = 'Request was blackholed. Type: ' . $type;
			$this->__Logger = new ExceptionLogger;
			$this->__Logger->write($message, $data);
			parent::__construct($message, 400);
		}

	}

