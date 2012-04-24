<div class="flash flash-error alert alert-error">
	<?php
	if (is_array($message))
		echo implode('</div><div class="flash error">', $message);
	else
		echo $message;
?>
</div>