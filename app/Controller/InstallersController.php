<?php

class InstallersController extends AppController {
	public $name = 'Installers';
	public $uses = NULL;

	public function index() {

	}

	public function database() {
		$db = ConnectionManager::getDataSource('default');
		if (!$db->isConnected()) {
			echo 'Could not connect to database. Please check the settings in app/config/database.php and try again';
			exit();
		}

		$this->__executeSQLScript($db, APP . 'Config' . DS . 'schema' . DS . 'schema.sql');
		$this->redirect('/installers/done');
	}

	public function done() {
		file_put_contents(APP . 'Config' . DS . 'installed.txt', date('Y-m-d, H:i:s'));
	}

	public function beforeFilter() {
		parent::beforeFilter();
		if (file_exists(APP . 'Config' . DS . 'installed.txt')) {
			echo 'Application already installed. Remove app/config/installed.txt to reinstall the application';
			exit();
		}
	}

	private function __executeSQLScript($db, $fileName) {
		$statements = file_get_contents($fileName);
		$statements = explode(';', $statements);

		foreach ($statements as $statement) {
			if (trim($statement) != '') {
				$db->query($statement);
			}
		}
	}

}
?>