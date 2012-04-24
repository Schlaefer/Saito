<div class="flash flash-notice alert alert-info">
	<?php
	if (is_array($message))
		echo implode('</div><div class="flash notice">', $message);
	else
		echo $message;
	?>
</div>