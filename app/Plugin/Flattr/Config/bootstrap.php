<?php

	App::uses('SaitoPlugin', 'Lib/Saito');
	App::uses('FlattrRenderer', 'Flattr.Lib');

	$settings = SaitoPlugin::loadConfig('Flattr');
	SaitoEventManager::getInstance()->attach(new FlattrRenderer($settings));
