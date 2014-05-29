<?php

	interface ForumsUserInterface {

		public function setSettings($user);

		public function getSettings();

		public function getId();

		public function isUser();

		public function isMod();

		public function isModOnly();

		public function isAdmin();

		public function isLoggedIn();

		public function isForbidden();

		public function getMaxAccession();

		public function mockUserType($type);

	}

