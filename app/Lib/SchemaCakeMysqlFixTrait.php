<?php

	App::uses('ClassRegistry', 'Utility');

	trait SchemaCakeMysqlFixTrait {

		/**
		 * Workaround for missing CakePHP (2) support for native MySQL types
		 *
		 * @see: https://github.com/cakephp/cakephp/issues/1918
		 */
		public function cakeMysqlMediumBlobFix() {
			App::uses('Ecach', 'Model');
			$Ecach = ClassRegistry::init('Ecach');
			$DS = $Ecach->getDataSource();
			if ($DS instanceof Mysql) {
				$table = $Ecach->tablePrefix . $Ecach->table;
				$DS->fetchAll('ALTER TABLE `' . $table . '` CHANGE `value` `value` MEDIUMBLOB  NOT NULL;');
			}
		}

	}