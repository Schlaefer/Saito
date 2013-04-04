<?php
	$messages = $this->JsData->getAppJsMessages();
	foreach($messages['msg'] as $message) :
	?>
  <div class="flash flash-<?php echo $message['type'] ?>">
			<div class="alert">
				<?php echo $message['message']; ?>
			</div>
  </div>
	<?php
	endforeach;
