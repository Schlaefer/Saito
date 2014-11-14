<?= $this->Html->docType('html5'); ?>
<html>
	<head>
		<title><?php echo $title_for_layout ?></title>
		<?php
			echo $this->Html->charset();
			echo $this->element('layout/script_tags', ['require' => false]);
			echo $this->Html->css([
				'/bootstrap/Vendor/sass-bootstrap/bootstrap-2.3.2.min',
				'stylesheets/static.css',
				'stylesheets/admin.css'
			]);
			echo $this->Html->script('bootstrap/bootstrap');
		?>
    <style type="text/css">
      div.submit {
				/*display: inline-block; margin: 0 1em;*/
			}
      .modal-footer form {
				margin: 0;
			}
    </style>
	</head>
	<body>
		<div class="container">
	<div class="navbar">
		<div class="navbar-inner">
			<div class="container" style="width: auto;">
				<?= $this->Html->link(__('Forum'), '/', ['class' => 'brand']); ?>
        <?php
          $jqueryVsBootstrapFix = ['onclick' => "$('.dropdown').removeClass('dropdown');"];
        ?>
				<ul class="nav">
					<li class="<?php  if (preg_match('/\/admin$/', $this->request->here)) { echo 'active'; }; ?>">
						<?= $this->Html->link(__('Overview'), '/admin/') ?>
					</li>
					<li class="<?php  if (stristr($this->request->here, 'settings')) { echo 'active'; }; ?>">
						<?php echo $this->Html->link(__('Settings'), '/admin/settings/index'); ?>
					</li>
					<li class="dropdown <?php  if (stristr($this->request->here, 'users')) { echo 'active'; }; ?>">
						<?php
              echo $this->Html->link(
                __('Users') . ' ▾',
                '/admin/users/index',
                ['class' => 'drowdown-toggle', 'data-toggle' => 'dropdown']
              );
              echo $this->Html->nestedList([
                  $this->Html->link(__('Users'),
                    '/admin/users/index',
                    $jqueryVsBootstrapFix),
                  $this->Html->link(__('user.block.history'),
                    '/admin/users/block',
                    $jqueryVsBootstrapFix)
                ],
                ['class' => 'dropdown-menu']);
            ?>
					</li>
					<li class="<?php  if (stristr($this->request->here, 'categories')) { echo 'active'; }; ?>">
						<?php echo $this->Html->link(__('Categories'), '/admin/categories/index'); ?>
					</li>
					<li class="<?php  if (stristr($this->request->here, 'smilies')) { echo 'active'; }; ?>">
						<?php echo $this->Html->link(__('Smilies'), '/admin/smilies/index'); ?>
					</li>
					<li class="dropdown <?php  if (stristr($this->request->here, 'stats')) { echo 'active'; }; ?>">
						<?php
              echo $this->Html->link(
                __('Stats') . ' ▾',
                '/admin/admins/stats',
                ['class' => 'drowdown-toggle', 'data-toggle' => 'dropdown']
              );
							echo $this->Html->nestedList([
											$this->Html->link(__('admin.stats.yearly'),
													'/admin/admins/stats',
													$jqueryVsBootstrapFix),
											$this->Html->link(__('admin.stats.detailed'),
													'/admin/admins/stats_details',
													$jqueryVsBootstrapFix)
									],
									['class' => 'dropdown-menu']);
						?>
					</li>
					<li class="<?php  if (stristr($this->request->here, 'logs')) { echo 'active'; }; ?>">
						<?php echo $this->Html->link(__('Logs'), '/admin/admins/logs'); ?>
					</li>

					<?php
						//= plugins
						$items = SaitoEventManager::getInstance()->dispatch(
							'Request.Saito.View.Admin.plugins'
						);
						if ($items) { ?>
					<li class="dropdown <?php  if (stristr($this->request->here, 'plugin')) { echo 'active'; }; ?>">
						<?php
								echo $this->Html->link(
									__('Plugins') . ' ▾',
									'/admin/plugins',
									['class' => 'drowdown-toggle', 'data-toggle' => 'dropdown']
								);
								foreach ($items as $item) {
									$plugins[] = $this->Html->link(
										$item['title'],
										$item['url'],
										$jqueryVsBootstrapFix
									);
								}
								echo $this->Html->nestedList(
									$plugins,
									['class' => 'dropdown-menu']
								);
							}
						?>

				</ul>
				<ul class="nav pull-right">
					<li class="divider-vertical"></li>
					<li>
						<a href="<?= Configure::read('Saito.saitoHomepage') ?>">
							Saito
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="span1">&nbsp;</div>
		<div class="span10">
			<?php
				echo $this->element('flash/render');
				echo $this->Html->getCrumbs(' > ');
				echo $this->fetch('content');
			?>
		</div>
		<div class="span1">&nbsp;</div>
	</div>
</div>
<?php
	echo $this->fetch('script');
	echo $this->Js->writeBuffer();
	echo $this->Html->script(['lib/datatables-bootstrap/datatables-bootstrap.js']);
	echo $this->element('layout/debug_footer');
?>
</body>
</html>