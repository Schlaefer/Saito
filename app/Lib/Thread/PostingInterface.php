<?php

	interface PostingInterface {

		public function __get($var);

		public function getChildren();

		public function getLevel();

		public function getRaw();

		public function isRoot();

		public function addDecorator($fct);

	}

