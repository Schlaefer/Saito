<?php echo $this->Html->docType('xhtml-trans'); ?>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $title_for_layout ?></title>
		<?php echo $this->Html->charset(); ?>
		<?php echo $this->Html->script(
				array(
						'lib/jquery/jquery-1.8.3.min.js',
						'bootstrap/bootstrap'
				)
				); ?>
		<?php echo $this->Html->css(
				array(
					'bootstrap/css/bootstrap.min.css',
					'stylesheets/admin.css',
					)
				); ?>
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
						<a class="brand" href="http://saito.siezi.com/">
							Saito
						</a>
						<ul class="nav">
							<li class="<?php  if (preg_match('/\/admin$/', $this->request->here)) { echo 'active'; }; ?>">
								<?php
								echo $this->Html->link(__('Overview'),
										array( 'controller' => 'admins', 'action' => 'index', 'admin' => true ));
								?>
							</li>
							<li class="<?php  if (stristr($this->request->here, 'settings')) { echo 'active'; }; ?>">
								<?php echo $this->Html->link(__('Settings'), '/admin/settings/index'); ?>
							</li>
							<li class="<?php  if (stristr($this->request->here, 'users')) { echo 'active'; }; ?>">
								<?php echo $this->Html->link(__('Users'), '/admin/users/index'); ?>
							</li>
							<li class="<?php  if (stristr($this->request->here, 'categories')) { echo 'active'; }; ?>">
								<?php echo $this->Html->link(__('Categories'), '/admin/categories/index'); ?>
							</li>
							<li class="<?php  if (stristr($this->request->here, 'smilies')) { echo 'active'; }; ?>">
								<?php echo $this->Html->link(__('Smilies'), '/admin/smilies/index'); ?>
							</li>
						</ul>
						<ul class="nav pull-right">
							<li class="divider-vertical"></li>
							<li>
								<?php
								echo $this->Html->link(__('Forum'),
										array( 'controller' => 'entries', 'action' => 'index', 'admin' => false ));
								?>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<?php
			$flashMessage = $this->Session->flash();
			$emailMessage = $this->Session->flash('email');
			if ( $flashMessage || $emailMessage ) :
				?>
				<div class="alert alert-info">
					<?php echo $flashMessage; ?>
					<?php echo $emailMessage; ?>
				</div>
			<?php endif; ?>

			<div class="row">
				<div class="span1">&nbsp;</div>
				<div class="span10">
					<?php echo $this->Html->getCrumbs(' > '); ?>
          <?php echo $this->fetch('content'); ?>
				</div>
				<div class="span1">&nbsp;</div>
			</div>
		</div>
		<?php echo $scripts_for_layout; ?>
		<?php echo $this->Js->writeBuffer(); ?>
		<?php echo $this->Html->script(
          array(
              'lib/datatables-bootstrap/datatables-bootstrap.js',
          )
      );

		?>
		<?php echo $this->element('sql_dump'); ?>
	</body>
</html>