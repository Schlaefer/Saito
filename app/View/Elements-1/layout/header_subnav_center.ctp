<? if (isset($paginator) && $this->request->params['action'] == 'index') : ?>
	<?php
		if ($CurrentUser->isLoggedIn()) :
			echo $this->Html->link('&nbsp;<div class="img_reload pointer"></div>&nbsp;', '/entries/update', 
					array(
							'id'			=> 'btn_manualy_mark_as_read',
							'escape' => false,
							'style'	=> "width: 100px; display: inline-block; height: 20px;",
							));
		endif;
	?>
<? endif; ?>