<?php

	//= load plugin settings from Config/config.php
	Configure::load('Userranks.config');
	$settings = Configure::read('Userranks');

	//= create Userranks class
	App::uses('Userranks', 'Userranks.Lib');
	$Userranks = new Userranks($settings);

	//= attach Userranks class to the Saito Event Manager
	App::uses('SaitoEventManager', 'Lib/Saito/Event');
	SaitoEventManager::getInstance()->attach($Userranks);

