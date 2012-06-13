<div id="admins_index" class="admins index">
	<h1>
		System-Info
	</h1>
	<p>
		You are running <strong>Saito version</strong>: <span class='label label-info'> <?php echo Configure::read('Saito.v'); ?></span>.
	</p>
	<p>
		Saito is convinced it's running on the <strong>server</strong>: <span class='label label-info'> <?php echo FULL_BASE_URL; ?></span>.
	</p>
	<p>
		Saito believes its <strong>base-URL</strong> is: <span class='label label-info'> <?php echo $this->request->webroot ?></span>.
	</p>

	<hr/>

	<h1> <?php echo __('User'); ?> </h1>
	<ul>
		<li>
			<?php echo $this->Html->link(__('Admin User Add'),
						'/admin/users/add'); ?>
		</li>
	</ul>
</div>