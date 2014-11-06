<?php

	namespace Saito\Posting;

	interface PostingInterface {

		public function get($var);

		public function getChildren();

		public function getLevel();

		public function getThread();

		public function hasAnswers();

		public function isNt();

		public function isPinned();

		public function getRaw();

		public function isRoot();

		public function addDecorator($fct);

	}

