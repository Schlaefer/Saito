<?php $this->Html->addCrumb(__('Settings'), '/admin/settings'); ?>
<?php
  $tableHeadersHtml = $this->Html->tableHeaders(array(
      __('Key'),
      __('Value'),
      __('Explanation'),
      __('Actions')
      ));
?>
<div id="settings_index" class="settings index">
	<h2><?php echo __('Deactivate Forum'); ?></h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
		<tr>
			<td>
				<?php echo __('forum_disabled'); ?>
			</td>
			<td>
				<?php echo $Settings['forum_disabled']; ?>
			</td>
			<td>
				<p><?php echo __('forum_disabled_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'forum_disabled' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('forum_disabled_text'); ?>
			</td>
			<td>
				<?php echo $Settings['forum_disabled_text']; ?>
			</td>
			<td>
				<p><?php echo __('forum_disabled_text_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'forum_disabled_text'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
  </table>

	<h2><?php echo __('Base Preferences'); ?></h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
    <tr>
			<td>
				<?php echo __('forum_name'); ?>
			</td>
			<td>
				<?php echo $Settings['forum_name']; ?>
			</td>
      <td>
				<p><?php echo __('forum_name_exp'); ?></p>
      </td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'forum_name' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
    </tr>
    <tr>
			<td>
				<?php echo __('forum_email'); ?>
			</td>
			<td>
				<?php echo $Settings['forum_email']; ?>
			</td>
      <td>
				<p><?php echo __('forum_email_exp'); ?></p>
      </td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'forum_email' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
    </tr>
		<tr>
			<td>
				<?php echo __('timezone'); ?>
			</td>
			<td>
				<?php echo $Settings['timezone']; ?>
			</td>
			<td>
				<p><?php echo __('timezone_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'timezone'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table>

	<h2><?php echo __('Moderators'); ?></h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
		<tr>
			<td>
				<?php echo __('block_user_ui'); ?>
			</td>
			<td>
				<?php echo $Settings['block_user_ui']; ?>
			</td>
			<td>
				<p><?php echo __('block_user_ui_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'block_user_ui' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table>

	<h2><?php echo __('Edit'); ?></h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
		<tr>
			<td>
				<?php echo __('edit_period'); ?>
			</td>
			<td>
				<?php echo $Settings['edit_period']; ?>
			</td>
			<td>
				<p><?php echo __('edit_period_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'edit_period' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->

	<h2><?php echo __('View'); ?></h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
		<tr>
			<td>
				<?php echo __('edit_delay'); ?>
			</td>
			<td>
				<?php echo $Settings['edit_delay']; ?>
			</td>
			<td>
				<p><?php echo __('edit_delay_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'edit_delay' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('topics_per_page'); ?>
			</td>
			<td>
				<?php echo $Settings['topics_per_page']; ?>
			</td>
			<td>
				<p><?php echo __('topics_per_page_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'topics_per_page' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('thread_depth_indent'); ?>
			</td>
			<td>
				<?php echo $Settings['thread_depth_indent']; ?>
			</td>
			<td>
				<p><?php echo __('thread_depth_indent_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'thread_depth_indent' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('autolink'); ?>
			</td>
			<td>
				<?php echo $Settings['autolink']; ?>
			</td>
			<td>
				<p><?php echo __('autolink_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'autolink' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('bbcode_img'); ?>
			</td>
			<td>
				<?php echo $Settings['bbcode_img']; ?>
			</td>
			<td>
				<p><?php echo __('bbcode_img_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'bbcode_img' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('quote_symbol'); ?>
			</td>
			<td>
				<?php echo $Settings['quote_symbol']; ?>
			</td>
			<td>
				<p><?php echo __('quote_symbol_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'quote_symbol' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('signature_separator'); ?>
			</td>
			<td>
				<?php echo $Settings['signature_separator']; ?>
			</td>
			<td>
				<p><?php echo __('signature_separator_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'signature_separator' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('subject_maxlength'); ?>
			</td>
			<td>
				<?php echo $Settings['subject_maxlength']; ?>
			</td>
			<td>
				<p><?php echo __('subject_maxlength_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'subject_maxlength' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('text_word_maxlength'); ?>
			</td>
			<td>
				<?php echo $Settings['text_word_maxlength']; ?>
			</td>
			<td>
				<p><?php echo __('text_word_maxlength_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'text_word_maxlength' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('userranks_show'); ?>
			</td>
			<td>
				<?php echo $Settings['userranks_show']; ?>
			</td>
			<td>
				<p><?php echo __('userranks_show_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'userranks_show' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('userranks_ranks'); ?>
			</td>
			<td>
				<?php echo $Settings['userranks_ranks']; ?>
			</td>
			<td>
				<p><?php echo __('userranks_ranks_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'userranks_ranks' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('video_domains_allowed'); ?>
			</td>
			<td>
				<?php echo $Settings['video_domains_allowed']; ?>
			</td>
			<td>
				<p><?php echo __('video_domains_allowed_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'video_domains_allowed' ),
										array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->
	<h2>Flattr</h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
		<tr>
			<td>
				<?php echo __('flattr_enabled'); ?>
			</td>
			<td>
				<?php echo $Settings['flattr_enabled']; ?>
			</td>
			<td>
				<p><?php echo __('flattr_enabled_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'flattr_enabled'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('flattr_language'); ?>
			</td>
			<td>
				<?php echo $Settings['flattr_language']; ?>
			</td>
			<td>
				<p><?php echo __('flattr_language_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'flattr_language'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('flattr_category'); ?>
			</td>
			<td>
				<?php echo $Settings['flattr_category']; ?>
			</td>
			<td>
				<p><?php echo __('flattr_category_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'flattr_category'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->
	<br/>
	<h2>Uploads</h2>
	<table class="table table-striped table-bordered table-condensed">
		<?php echo $tableHeadersHtml ?>
		<tr>
			<td>
				<?php echo __('upload_max_img_size'); ?>
			</td>
			<td>
				<?php echo $Settings['upload_max_img_size']; ?>
			</td>
			<td>
				<p><?php echo __('upload_max_img_size_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'upload_max_img_size'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __('upload_max_number_of_uploads'); ?>
			</td>
			<td>
				<?php echo $Settings['upload_max_number_of_uploads']; ?>
			</td>
			<td>
				<p><?php echo __('upload_max_number_of_uploads_exp'); ?></p>
			</td>
			<td>
				<?php echo $this->Html->link(
								__('edit'),
								array( 'controller' => 'settings', 'action' => 'edit', 'upload_max_number_of_uploads'),
								array( 'class' => 'btn' )
							);
				?>
			</td>
		</tr>
	</table> <!--	</table>-->
</div>
