<?php

	App::uses('ForumsUserInterface', 'Lib/SaitoUser');
	App::uses('SaitoUserTrait', 'Lib/SaitoUser');

	class SaitoUser implements ForumsUserInterface, ArrayAccess {

		use SaitoUserTrait;

	}
