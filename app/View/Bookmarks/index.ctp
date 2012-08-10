<?php
	$this->start('headerSubnavLeft');
	echo $this->Html->link(
			'<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'), '/',
			array('class'	 => 'textlink', 'escape' => FALSE));
	$this->end();
?>
<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?php echo $this->TextH->properize($CurrentUser['username']) . ' ' . __('Bookmarks'); ?></h1> </div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<?php if ($bookmarks): ?>
				<div class="l-bookmarks-container">
					<div class="l-bookmarks-row bookmarks-row-header">
						<div class="l-bookmarks-cell bookmarks-cell" style="width: 16px">
						</div>
						<div class="l-bookmarks-cell bookmarks-cell" style="width: 60%">
							<?php echo __('Subject'); ?>
						</div>
						<div class="l-bookmarks-cell bookmarks-cell" style="width: 16px">
						</div>
						<div class="l-bookmarks-cell bookmarks-cell" style="width: 30%">
							<?php echo __('Comment'); ?>
						</div>
					</div>
					<?php
					foreach ($bookmarks as $bookmark) {
						$entry_sub = array(
								'Entry'		 => $bookmark['Entry'],
								'Category' => $bookmark['Entry']['Category'],
								'User'		 => $bookmark['Entry']['User'],
						);
						?>
						<div id="bookmark-row-<?php echo $bookmark['Bookmark']['id']; ?>" class="l-bookmarks-row">
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 16px">
								<?php
								echo $this->Html->link(
										'<i class="icon-trash icon-large"></i>', '#',
										array(
												'data-id'	 => $bookmark['Bookmark']['id'],
												'class'		 => 'btn-bookmark-delete',
												'escape'	 => false,
												'title'		 => __('Delete'),
										)
								);
								?>			
							</div>
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 60%">
								<?php
								$thread		 = $this->element('entry/thread_cached',
										array(
										'entry_sub'	 => $entry_sub, 'level'			 => 0));
								echo "<a name={$bookmark['Entry']['id']}></a>" . $thread;
								?>
							</div>
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 16px">
								<?php
								echo $this->Html->link(
										'<i class="icon-edit icon-large"></i>',
										array(
										'controller' => 'bookmarks',
										'action'		 => 'edit',
										$bookmark['Bookmark']['id']
										),
										array(
										'escape' => false,
										'title'	 => __('btn-comment-title'),
										)
								);
								?>
							</div>
							<div class="l-bookmarks-cell bookmarks-cell" style="width: 30%">
								<?php
								echo $bookmark['Bookmark']['comment'];
								?>
							</div>
						</div>
					<?php } ?>
				<?php else: ?>
					<?php
					echo $this->element('generic/no-content-yet',
							array(
							'message' => __('No bookmarks created yet.')));
					?>
			<?php endif; ?>
		</div>
	</div>
	<?php
		echo $this->Html->scriptBlock(<<<EOF
$(document).ready(function (){
	var bookmarks = {
		delete: function (id) {
			$("#bookmark-row-" + id).hide("slide", null, 500, function(){ $(this).remove();});
		}
	};
	$("#content").on("click", ".btn-bookmark-delete", function (event) {
		var id = $(this).data('id');
		$.ajax({
			async:true,
			data:"id=" + id,
			dataType:"json",
			success:function (data, textStatus) {
				bookmarks.delete(id);
				},
			type:"POST",
			url:"{$this->webroot}bookmarks/delete"
		});
		return false;});
	});
EOF
		);
	?>
