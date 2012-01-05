<div id="admins_index" class="admins index box_style_2 box_layout_1">
	<h1> <?php __('Settings'); ?> </h1>
	<ul class="style_1" >
		<li>
			<?php echo $html->link(__('Admin Settings Index', true), '/admin/settings/index'); ?>
		</li>
	</ul>
	<h1> <?php echo __('User', true); ?> </h1>
	<ul class="style_1" >
		<li>
				<?php echo $html->link(__('Admin User Add', true), '/admin/users/add'); ?>
		</li>
	</ul>
	<h1> <?php echo __('Categories', true); ?> </h1>
	<ul class="style_1" >
		<li>
				<?php echo $html->link(__('Admin Category Index', true), '/admin/categories/index'); ?>
		</li>
	</ul>
	<h1> <?php echo __('Smilies', true) ?> </h1>
	<ul class="style_1" >
		<li>
			<?php echo $html->link(__('Admin Smiley Index', true), '/admin/smilies/index'); ?>
		</li>
	</ul>
</div>