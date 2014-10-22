<?php
	$this->Html->addCrumb(__('Settings'), '/admin/settings');
  $tableHeadersHtml = $this->Setting->tableHeaders();

	$this->start('settings');
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
					['forum_email', 'email_contact', 'email_register', 'email_system'],
					$Settings, ['sh' => 6]
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
					__('API'),
					['api_enabled', 'api_crossdomain'],
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
					$Settings,
					['nav-title' => 'Flattr']
				);

				echo $this->Setting->table(
					$this->Html->link('Embed.ly', 'http://embed.ly/'),
					['embedly_enabled', 'embedly_key'],
					$Settings,
					['nav-title' => 'Embedly']
				);

				echo $this->Setting->table(
						$this->Html->link(__('admin.set.map'), 'http://developer.mapquest.com/'),
						['map_enabled', 'map_api_key'],
						$Settings,
						['nav-title' => __('admin.set.map')]
				);

				echo $this->Setting->table(
					__('Debug'),
					['stopwatch_get'],
					$Settings
				);
	$this->end('settings');
?>
<div id="settings_index" class="settings index">
	<div class="row">
		<div class="span2 navbarsidelist">
			<ul class="nav nav-list affix" style="margin-top: 10px; width: 120px; padding-right: 0px; font-size: 13px">
				<?php foreach($this->Setting->getHeaders() as $key => $title): ?>
						<li>
							<a href="#navHeaderAnchor<?= $key ?>">
									<?= $title ?>
							</a>
						</li>
					<?php endforeach; ?>
			</ul>
		</div>
		<div class="span8">
			<h1><?php echo __('Settings'); ?></h1>
			<?= $this->fetch('settings') ?>
		</div>
	</div>
</div>
<script>
	var $body = document.getElementsByTagName('body')[0];
	$body.setAttribute('data-spy', 'scroll');
	$body.setAttribute('data-target', '.navbarsidelist');
	delete $body;
</script>
