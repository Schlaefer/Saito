<?php
	$this->element('users/menu');

	echo $this->Html->div('users map', $this->Map->map($users));
