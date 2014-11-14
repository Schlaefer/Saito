<?php

	class SiegeShell extends AppShell {

		public $uses = [
				'Entry'
		];

		protected $_prefix = '$(HOST)/';

		public function main() {
			$out = [];
			$base = rtrim($this->args[0], '/');
			$in = $this->in('Base URL is: ' . $base, ['y', 'n'], 'y');
			if ($in !== 'y') {
				$this->out('Aborting.');
				return;
			}
			$out[] = 'HOST=' . $base;
			$this->_generateEntriesIndex($out);
			$this->_generateEntriesMix($out);
			$this->_generateEntriesView($out);
			$this->_siege($out);
		}

		public function getOptionParser() {
			$parser = parent::getOptionParser();
			$parser->addArgument('url',
					[
							'help' => 'Saito base URL',
							'required' => true
					]);
			return $parser;
		}

		protected function _siege(&$out) {
			$urlFilePath = TMP . 'url.txt';
			$this->_cleanup($urlFilePath);
			$this->createFile($urlFilePath, implode("\n", $out));
			$command = 'siege -R ' . APP . '..' . DS . ".siegerc -f $urlFilePath";
			$this->out('<info>Running: ' . $command . '</info>', 1, 'info');
			exec($command);
		}

		protected function _cleanup($urlFilePath) {
			if (file_exists($urlFilePath)) {
				unlink($urlFilePath);
			}
		}

		protected function _generateEntriesIndex(&$out) {
			for ($i = 0; $i < 400; $i++) {
				$out[] = $this->_prefix . 'entries/index/page:' . rand(1, 2);
			}
			for ($i = 0; $i < 400; $i++) {
				$out[] = $this->_prefix . 'entries/index/page:' . rand(5, 10);
			}
		}

		protected function _generateEntriesMix(&$out) {
			$entries = $this->Entry->find('all',
					[
							'fields' => ['Entry.id'],
							'conditions' => ['Entry.pid' => 0, 'Category.accession' => 1],
							'limit' => '500'
					]);

			foreach ($entries as $entry) {
				$out[] = $this->_prefix . 'entries/mix/' . $entry['Entry']['id'];
			}
		}

		protected function _generateEntriesView(&$out) {
			$entries = $this->Entry->find('all',
					[
							'fields' => ['Entry.id'],
							'conditions' => ['Category.accession' => 1],
							'limit' => '1000'
					]);

			foreach ($entries as $entry) {
				$out[] = $this->_prefix . 'entries/view/' . $entry['Entry']['id'];
			}
		}

	}

