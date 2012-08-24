<style type="text/css">
	body > div { min-height: inherit; height: 100%;  }
	body {
		position:relative;
		background-color: #ffe99d;
	}
	#top {
		height: 700px;
		width: 300px;
		background: none;
		background-image: url('<?php echo $this->request->webroot; ?>theme/macnemo/img/bubbles_right.png');
		background-position: bottom left;
		background-repeat: no-repeat;
		position: fixed;
		z-index: 10;
		left: 50px;
		top: 0px;
	}
	#top2 {
		height: 100%;
		width: 100%;
		background-image: url('<?php echo $this->request->webroot; ?>theme/macnemo/img/bubbles_outer_large.png');
		position: fixed;
		z-index: 5;
	}
</style>
<div id="top"></div>
<div id="top2"></div>
<div style="height: 100%; width:100%" class="header_style_dark">
	<div style="text-align:center; padding-top:50px; z-index: 15; position: relative; margin: 0 auto; width: 600px;">
		<p>
			<?php // echo $this->Html->image('forum_disabled.png'); ?>
		<div id="forumdisabled_hype_container" style="position:relative;overflow:hidden;width:600px;height:900px;">
			<?php echo $this->Html->script('forum_disabled_Resources/forumdisabled_hype_generated_script'); ?>
		</div>
		</p>
		<div style="margin-top: -450px;">
			<p>

				<?php echo Configure::read('Saito.Settings.forum_disabled_text'); ?>
			</p>
			<p >
				<a href="http://macnemo.de/wiki/">Wiki</a> | <a href="aim:gochat?roomname=macnemo">Plauderecke</a> | <a href="http://macnemo.de/wiki/index.php/Main/Impressum">Impressum</a>
			</p>
		</div>
	</div>
</div>