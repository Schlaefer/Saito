<div data-role="page" data-add-back-btn="true">

	<div data-role="header">
		<h1><?php echo $title_for_layout ?></h1>
		<?php
		echo $this->Html->link(
				'Back',
				'#',
				array(
					'class' => 'ui-btn-left',
					'data-icon' => 'arrow-l',
					'data-rel'	=> 'back',
					'data-iconpos'	=> 'notext',
				)
		);
		echo $this->Html->link(
				'Classic',
				'/entries/mix/' . $entries[0]['Entry']['tid'] . '#' . $entries[0]['Entry']['id'],
				array(
					'class' => 'ui-btn-right',
					'data-icon' => "arrow-r",
					'data-ajax' => 'false',
				)
		);
		?>
	</div><!-- /header -->

	<div class="mobile_mix" data-role="content">
			<?php  echo $this->element('entry/mobile_mix', array ( 'entry_sub' => $entries[0], 'level' => 0 )) ; ?>
			<?php
				if ( isset($this->passedArgs['jump']) ):
					echo "<script type='text/javascript'>"
							. "$('div[data-role=page]').live('pageshow', function (event) {"
							." var newPosition = $('a[name={$this->passedArgs['jump']}]').offset();"
//							. "window.scrollTo(0, newPosition.top);"
							. "$.mobile.silentScroll(newPosition.top);"
//							."window.history.replaceState('object or string', 'Title', window.location.pathname.replace(/jump:\d+(\/)?/,''));"

							. " });"
							. "</script>";
				endif;
			?>
	</div><!-- /content -->