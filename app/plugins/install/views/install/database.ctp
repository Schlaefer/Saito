<div class="install form">
	<h2><?php echo $title_for_layout; ?></h2>
	<?php
	echo $this->Form->create('Install', array( 'url' => array( 'plugin' => 'install', 'controller' => 'install', 'action' => 'database' ) ));
	echo $this->Form->input('Install.driver', array(
			'label' => 'Driver',
			'div' => array( 'class' => 'required' ),
			'value' => 'mysql',
			'empty' => false,
			'options' => array(
					'mysql' => 'mysql',
					'mysqli' => 'mysqli',
					'sqlite' => 'sqlite',
					'postgres' => 'postgres',
					'mssql' => 'mssql',
					'db2' => 'db2',
					'oracle' => 'oracle',
					'firebird' => 'firebird',
					'sybase' => 'sybase',
					'odbc' => 'odbc',
			),
	));
	//echo $this->Form->input('Install.driver', array('label' => 'Driver', 'value' => 'mysql'));
	echo $this->Form->input('Install.host', array( 'label' => 'Host', 'div' => array( 'class' => 'required' ), 'value' => 'localhost', 'class' => 'title' ));
	echo $this->Form->input('Install.login', array( 'label' => 'User / Login', 'div' => array( 'class' => 'required' ), 'value' => 'root', 'class' => 'title'  ));
	echo $this->Form->input('Install.password', array( 'label' => 'Password', 'div' => array( 'class' => 'required' ), 'class' => 'title'  ));
	echo $this->Form->input('Install.database', array( 'label' => 'Database Name', 'div' => array( 'class' => 'required' ), 'class' => 'title'  ));
	echo $this->Form->input('Install.prefix', array( 'label' => 'Prefix for Table Names', 'value' => '', 'class' => 'title'  ));
	echo $this->Form->input('Install.port', array( 'label' => 'Port (leave blank if unknown)', 'class' => 'title'  ));
	echo $this->Form->end('Submit');
	?>
</div>