<?php

	class SettingsController extends AppController {

		public $name = 'Settings';
		public $helpers = array(
				'TimeH',
		);

		/**
		 * Subset of MLF settings currently used by Saito
		 */
		protected $_currentlyUsedSettings = array(
				'autolink'											 => 1,
				'bbcode_img'										 => 1,
				'block_user_ui'									 => 1,
				/**
				 * Activates and deactivates the category-chooser on entries/index
				 */
				'category_chooser_global'				 => 1,
				/**
				 * Allows users to show the category-chooser even if the default
				 * setting `category_chooser_global` is off
				 */
				'category_chooser_user_override' => 1,
				'edit_delay'										 => 1,
				'edit_period'										 => 1,
				'embedly_enabled'								 => 1,
				'embedly_key'										 => 1,
				'flattr_enabled'								 => 1,
				'flattr_language'								 => 1,
				'flattr_category'								 => 1,
				'forum_disabled'								 => 1,
				'forum_disabled_text'						 => 1,
				'forum_email'										 => 1,
				'forum_name'										 => 1,
				'quote_symbol'									 => 1,
				'signature_separator'						 => 1,
				'smilies'												 => 1,
				'stopwatch_get'									 => 1,
				'store_ip'											 => 1,
				'store_ip_anonymized'						 => 1,
				'subject_maxlength'							 => 1,
				'tos_enabled'										 => 1,
				'tos_url'												 => 1,
				'text_word_maxlength'						 => 1,
				'thread_depth_indent'						 => 1,
				'timezone'											 => 1,
				'topics_per_page'								 => 1,
				'upload_max_img_size'						 => 1,
				'upload_max_number_of_uploads'	 => 1,
				'userranks_ranks'								 => 1,
				'userranks_show'								 => 1,
				'video_domains_allowed'					 => 1,
		);

		public function admin_index() {
			$settings = $this->request->data = $this->Setting->getSettings();
			$settings = array_intersect_key($settings, $this->_currentlyUsedSettings);
			$this->set('Settings', $settings);
		}

		public function admin_edit($id = NULL) {
			if ( !$id ) {
				$this->redirect(array( 'action ' => 'index' ));
			}

			$this->Setting->id = $id;

			if ( empty($this->request->data) ) {
				$this->request->data = $this->Setting->read();
				if ( empty($this->request->data) ) {
					$this->Session->setFlash("Couldn't find parameter: {$id}", 'flash/error');
					$this->redirect(array(
							'controller' => 'settings', 'action' => 'index', 'admin' => true )
					);
				}
				if ( $id === 'timezone' ) :
					$this->render('admin_timezone');
				endif;
			} else {
				$this->Setting->id = $id;
				if ( $this->Setting->save($this->request->data) ) {
					$this->Session->setFlash('Saved. @lo', 'flash/notice');
					$this->redirect(array( 'action' => 'index', $id ));
				} else {
					$this->Session->setFlash('Something went wrong @lo', 'flash/error');
				}
			}
		}

	}

?>