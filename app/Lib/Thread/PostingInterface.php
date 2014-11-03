<?php

	interface PostingInterface {

		public function __get($var);

		public function getChildren();

		public function getLevel();

		public function hasAnswers();

		public function isNt();

		public function isPinned();

		public function getRaw();

		public function isRoot();

		public function addDecorator($fct);

	}

