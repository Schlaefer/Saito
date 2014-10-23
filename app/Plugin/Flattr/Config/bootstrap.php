<?php

	Configure::load('Flattr.config');
	$settings = Configure::read('Flattr');

	App::uses('FlattrRenderer', 'Flattr.Lib');
	SaitoEventManager::getInstance()->attach(new FlattrRenderer($settings));
