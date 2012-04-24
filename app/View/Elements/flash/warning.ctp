<div class="flash flash-warning alert">
	<?php
	if (is_array($message))
		echo implode('</div><div class="flash warning">', $message);
	else
		echo $message;
?>
</div>