<?php

	App::uses('DataSource', 'Model/Datasource');
	App::uses('File', 'Utility');
	App::uses('Folder', 'Utility');

	class SaitoHelpSource extends DataSource {

		protected $_schema = [
				'id' => [
						'type' => 'string',
						'null' => false,
						'key' => 'primary',
						'length' => 255
				],
				'text' => [
						'type' => 'text',
				]
		];

		public function calculate($model, $func, $params) {
			return 'COUNT';
		}

		public function listSources($data = null) {
			return null;
		}

		public function describe($model) {
			return $this->_schema;
		}

		public function resolveKey(Model $model, $key) {
			if (strpos('.', $key) === false) {
				return $model->alias . '.' . $key;
			}
			return $key;
		}

		public function read(Model $model, $queryData = [], $recursive = null) {
			// normalize conditions to `Model.field`
			if ($queryData['conditions']) {
				foreach ($queryData['conditions'] as $key => $value) {
					if (is_string($key)) {
						$queryData['conditions'][$this->resolveKey($model, $key)] = $value;
						unset($queryData['conditions'][$key]);
					}
				}
			}

			$queryData['conditions'] += [
					$this->resolveKey($model, 'language') => 'eng'
			];

			$id = $queryData['conditions'][$this->resolveKey($model, 'id')];
			list($plugin, $id) = pluginSplit($id);

			$lang = $queryData['conditions'][$this->resolveKey($model, 'language')];

			if ($plugin) {
				$folderPath = CakePlugin::path($plugin);
			} else {
				$folderPath = ROOT . DS;
			}
			$folderPath .= 'docs' . DS . 'help' . DS . $lang;

			$folder = new Folder($folderPath);
			$files = $folder->find("$id(-.*?)?\.md");
			if (!$files) {
				return false;
			}
			$name = $files[0];
			$file = new File($folderPath . DS . $name, false, 0444);
			$text = $file->read();
			$file->close();
			$result = [
				$model->alias => [
					'file' => $name,
					'id' => $id,
					'lang' => $lang,
					'text' => $text
				]
			];
			if (!empty($queryData['limit'])) {
				$result = [0 => $result];
			}
			return $result;
		}

	}