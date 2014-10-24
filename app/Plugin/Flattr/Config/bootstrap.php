<?php

	//= don't activate on CLI-tests
	if (php_sapi_name() === 'cli') {
		return;
	}

	App::uses('SaitoPlugin', 'Lib/Saito');
	App::uses('FlattrRenderer', 'Flattr.Lib');

	$settings = SaitoPlugin::loadConfig('Flattr');
	SaitoEventManager::getInstance()->attach(new FlattrRenderer($settings));
