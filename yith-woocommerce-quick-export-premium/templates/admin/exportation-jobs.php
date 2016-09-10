<?php
if (isset($_GET["create-job"])) :
	include( YITH_YWQE_TEMPLATES_DIR . 'admin/create-job.php' );
else:
	include( YITH_YWQE_TEMPLATES_DIR . 'admin/jobs-table.php' );
endif;
?>