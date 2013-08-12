<?php

	App::uses('ExceptionRenderer', 'Error');

	class ApiExceptionRenderer extends ExceptionRenderer {

		public function error400($error) {
			$this->error($error);
		}

		public function error500($error) {
			$this->error($error);
		}

		public function error($error) {
			$message = $error->getMessage();
			$url  = $this->controller->request->here();

			$code = (($error->getCode() > 500 && $error->getCode(
							) < 506) || $error->getCode() < 500) ? $error->getCode() : 500;
			if (empty($code)) {
				$code = 500;
			}
			if (!Configure::read('debug') && $error instanceof CakeException) {
				$genericApiError = new \Saito\Api\GenericApiError;
				$message = $genericApiError->getMessage();
				$code = $genericApiError->getCode();
			}
			$this->controller->response->statusCode($code);
			$this->controller->viewClass = 'Json';
			$this->controller->set(
				array(
					'message' => h($message),
					'url'     => h($url),
					'error'   => $error,
				)
			);
			$this->_outputMessage('apierror');
		}

	}
