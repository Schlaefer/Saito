{
	error: {
		<?php
			if (empty($error) === false) {
				echo json_encode($error);
			}
		?>
	}
}