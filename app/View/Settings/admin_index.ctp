<?php
	$this->Html->addCrumb(__('Settings'), '/admin/settings');
  $tableHeadersHtml = $this->Setting->tableHeaders();
?>
<div id="settings_index" class="settings index">
	<h1><?php echo __('Settings'); ?></h1>
	<?php

		echo $this->Setting->table(
			__('Deactivate Forum'),
			['forum_disabled', 'forum_disabled_text'],
			$Settings
		);

		echo $this->Setting->table(
			__('Base Preferences'),
			['forum_name', 'timezone'],
			$Settings
		);

		echo $this->Setting->table(
			__('Email'),
			['forum_email'],
			$Settings
		);

		echo $this->Setting->table(
			__('Moderation'),
			['block_user_ui', 'store_ip', 'store_ip_anonymized'],
			$Settings
		);

		echo $this->Setting->table(
			__('Registration'),
			['tos_enabled', 'tos_url'],
			$Settings
		);

		echo $this->Setting->table(
			__('Edit'),
			['edit_period', 'edit_delay'],
			$Settings
		);

		echo $this->Setting->table(
			__('View'),
			[
				'topics_per_page',
				'thread_depth_indent',
				'autolink',
				'bbcode_img',
				'quote_symbol',
				'signature_separator',
				'subject_maxlength',
				'text_word_maxlength',
				'userranks_show',
				'userranks_ranks',
				'video_domains_allowed'
			],
			$Settings
		);

		echo $this->Setting->table(
			__('Category Chooser'),
			['category_chooser_global', 'category_chooser_user_override'],
			$Settings
		);

		echo $this->Setting->table(
			__('Shoutbox'),
			['shoutbox_enabled', 'shoutbox_max_shouts'],
			$Settings
		);

		echo $this->Setting->table(
			__('Uploads'),
			['upload_max_img_size', 'upload_max_number_of_uploads'],
			$Settings
		);

		echo $this->Setting->table(
			$this->Html->link('Flattr', 'http://flattr.com/'),
			['flattr_enabled', 'flattr_language', 'flattr_category'],
			$Settings
		);

		echo $this->Setting->table(
			$this->Html->link('Embed.ly', 'http://embed.ly/'),
			['embedly_enabled', 'embedly_key'],
			$Settings
		);

		echo $this->Setting->table(
			__('Debug'),
			['stopwatch_get'],
			$Settings
		);
	?>
</div>
