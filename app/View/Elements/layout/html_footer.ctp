<div>
  <?php
		echo $this->Html->scriptBlock("
			var webroot = '{$this->request->webroot}';
			var Saito_Settings_embedly_enabled = " . $this->Js->value(Configure::read('Saito.Settings.embedly_enabled')) .";
			var User_Settings_user_show_inline = " . $this->Js->value($CurrentUser['inline_view_on_click']) . ";
		");
  ?>
  <?php
    if ( Configure::read('debug') == 0 ):
      echo $this->Html->script('js.min');
      echo $this->Html->script(
          array(
              'js2-min.js',
          )
      );
    else:
      echo $this->Html->script('jquery.hoverIntent.minified');
      echo $this->Html->script('lib/jquery-ui/jquery-ui-1.8.22.custom.min');
      echo $this->Html->script('classes/thread.class');
      echo $this->Html->script('classes/thread_line.class');
      echo $this->Html->script('_app');
      echo $this->Html->script('jquery.scrollTo-1.4.2-min');
      echo $this->Html->script(
          array(
              'bootstrap/bootstrap.js',
              'lib/underscore/underscore.js',
              'lib/backbone/backbone.js',
              'lib/backbone/backbone.localStorage',
              '_appbb'
          )
      );
    endif;
  ?>
  <?php echo $this->fetch('script'); ?>
  <?php echo $this->Js->writeBuffer(); ?>
  <div class='clearfix'></div>
  <?php
		if ($showStopwatchOutput) {
			echo $this->Html->tag('div', $this->Stopwatch->getResult(), array('style' => 'float: left;'));
			echo $this->Html->tag('div', $this->Stopwatch->plot(), array('style' => 'float: left; margin-left: 2em;'));
		}
  ?>
<?php echo $this->element('sql_dump'); ?>
</div>