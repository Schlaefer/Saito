<?php
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarBack();
	$this->end();

	$this->element('users/menu');

	echo $this->Html->div('users map', $this->Map->map($users));
