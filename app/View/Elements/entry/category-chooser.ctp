<?php Stopwatch::start('category-chooser.ctp'); ?>
<div id="category-chooser" style="display: none; overflow: hidden;">
	<div style="float:right; width: 150px;">
		<p>
			Choose your default categories.
		</p>
		<p>
			Click on a name to quickly show this category without changing you preferences.
		</p>
	</div>
	<?=
		$this->Form->create(null,
				array(
				'url' => array('controller' => 'entries', 'action'		 => 'setcategory'),
				'style'			 => 'clear: none;',
		));
	?>
	<ul>
		<?php foreach ($categoryChooser as $key => $title): ?>
				<li>
					<?=
					$this->Form->checkbox('CatChooser.' . $title,
							array(
							'hiddenField'	 => false,
							'checked'			 => isset($CurrentUser['user_category_custom'][$key]),
							'value'				 => $key));
					?>
					<?= $this->Html->link($title, '/entries/setcategory/'.$key ) ?>
				</li>
			<?php endforeach; ?>
	</ul>
	<?=
		$this->Form->submit(__('Apply'), array(
				'class' => 'btn btn-submit'))
	?>
	<?= $this->Form->end() ?>
</div>
<?php Stopwatch::end('category-chooser.ctp'); ?>
