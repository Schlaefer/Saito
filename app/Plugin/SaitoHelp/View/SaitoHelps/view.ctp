<div class="panel">
	<?= $this->Layout->panelHeading($title_for_page, ['pageHeading' => true]) ?>
	<div class="panel-content staticPage">
		<?php
			echo $this->Html->css('SaitoHelp.saitohelp');
			echo $this->SaitoHelp->parse($text, $CurrentUser);
		?>
	</div>
</div>
