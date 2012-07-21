<?php Stopwatch::start('category-chooser.ctp'); ?>
<div id="category-chooser" style="display: none; overflow: hidden;">
	<div class="box_layout_1 box-form">
		<div style="float:right; width: 150px;">
			<p>
				<?php echo __('category_chooser_context_exp'); ?>
			</p>
		</div>
		<?php echo $this->Form->create(null,
					array(
					'url' => array('controller' => 'entries', 'action'		 => 'setcategory'),
					'style'			 => 'clear: none;',
			));
		?>
		<ul class="category-chooser-ul">
			<?php foreach ($categoryChooser as $key => $title): ?>
					<li class="category-chooser-li">
						<?php
						echo $this->Form->checkbox('CatChooser.' . $key,
								array(
								'onclick'			 => "$('#cb-category-chooser-all').removeAttr('checked')",
								'checked'			 => isset($categoryChooserChecked[$key]),
								'value'				 => 1));
						?>
						<?php echo $this->Html->link($title, '/entries/setcategory/' . $key) ?>
					</li>
				<?php endforeach; ?>
			<li>
				<hr/>
			</li>
				<li class="category-chooser-li">
				<?php echo $this->Form->checkbox('CatMeta.All',
							array(
							'id'		 => 'cb-category-chooser-all',
							'value'	 => 1));
				?>
				<?php echo $this->Html->link(__('All'), '/entries/setcategory/all') ?>
			</li>
		</ul>
		<?php
			$this->Js->get('#cb-category-chooser-all')->event('click',
					<<<EOF
			if (this.checked) {
				$('#category-chooser').find(':checkbox').attr('checked', 'checked');
			} else {
				$('#category-chooser').find(':checkbox').removeAttr('checked');
			}
			return true;
EOF
			);
		?>
		<?php echo
			$this->Form->submit(__('Apply'), array(
					'class' => 'btn btn-submit'))
		?>
<?php echo $this->Form->end() ?>
	</div>
</div>
<?php Stopwatch::end('category-chooser.ctp'); ?>
