<?php Stopwatch::start('category-chooser.ctp'); ?>
<div id="category-chooser" style="display: none; overflow: hidden;">
	<div class="box_layout_1 box-form">
		<?php echo $this->Form->create(null,
					array(
					'url' => array('controller' => 'entries', 'action'		 => 'setcategory'),
					'style'			 => 'clear: none;',
			));
		?>
		<div style="float:right; width: 150px; margin-left: 2em;">
			<p>
				<?php echo __('category_chooser_context_exp'); ?>
			</p>
		</div>

		<ul class="category-chooser-ul">
			<li class="category-chooser-li">
				<?php 
					/* For performance reasons we generate the html manually */
					/*
					echo $this->Form->checkbox('CatMeta.All',
							array(
							'id'		 => 'cb-category-chooser-all',
							'style'  => 'visibility: hidden;',
							'value'	 => 1));
					 */
				?>
				<input type="hidden" name="data[CatMeta][All]" id="cb-category-chooser-all_" value="0">
				<input type="checkbox" name="data[CatMeta][All]" id="cb-category-chooser-all" style="visibility: hidden;" value="1">
				<?php
					/* For performance reasons we generate the html manually */
					/*
					echo $this->Html->link(__('All'), '/entries/setcategory/all')
					 */
				?>
				<a href="<?php echo $this->webroot; ?>entries/setcategory/all"><?php echo __('All'); ?></a>

			</li>
			<?php foreach ($categoryChooser as $key => $title): ?>
					<li class="category-chooser-li">
						<?php
						/* For performance reasons we generate the html manually */
							/*
						echo $this->Form->checkbox('CatChooser.' . $key,
								array(
								'onclick'			 => "$('#cb-category-chooser-all').removeAttr('checked')",
								'checked'			 => isset($categoryChooserChecked[$key]),
								'value'				 => 1));
							 *
							 */
						?>
						<input type="hidden" name="data[CatChooser][<?php echo $key; ?>]" id="CatChooser<?php echo $key; ?>_" value="0">
						<input type="checkbox" name="data[CatChooser][<?php echo $key; ?>]"
									 onclick="$('#cb-category-chooser-all').removeAttr('checked')" value="1" id="CatChooser<?php echo $key; ?>">
						<?php
							/* For performance reasons we generate the html manually */
							/*
							echo $this->Html->link($title, '/entries/setcategory/' . $key)
							 *
							 */
						?>
						<a href="<?php echo $this->webroot; ?>entries/setcategory/<?php echo $key; ?>"><?php echo $title; ?></a>

					</li>
				<?php endforeach; ?>
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
