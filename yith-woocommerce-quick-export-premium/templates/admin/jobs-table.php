<?php

$cron_jobs = _get_cron_array();

?>
<h2><?php _e( 'Scheduled task list', 'yith-woocommerce-quick-export' ); ?>
	<a href="<?php echo esc_url( add_query_arg( 'create-job', 'new' ) ); ?>"
	   class="add-new-h2"><?php _e( 'Add job', 'yith-woocommerce-quick-export' ); ?></a></h2>

<table class="widefat">
	<thead>
	<tr>
		<th><?php _e( 'ID', 'yith-woocommerce-quick-export' ); ?></th>
		<th><?php _e( 'Name', 'yith-woocommerce-quick-export' ); ?></th>
		<th><?php _e( 'Exportation time', 'yith-woocommerce-quick-export' ); ?></th>
		<th><?php _e( 'Recurrency', 'yith-woocommerce-quick-export' ); ?></th>
		<th colspan="3">&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $cron_jobs as $key => $cron_job ) {
		$next_time = $key;
		foreach ( $cron_job as $name => $value ) {
			if ( "ywqe_scheduled_export" != $name ) {
				continue;
			}

			foreach ( $value as $sig => $details ) {
				$args = $details["args"];
				$job  = unserialize( $args[0] );
				?>
				<tr id="scheduled-job-<?php echo $job->id; ?>">
					<td><?php echo $job->id; ?></td>
					<td><?php echo $job->name; ?></td>
					<td><?php echo $job->export_on_date . " " . $job->export_on_time; ?></td>
					<td><?php echo $job->recurrency; ?></td>

					<td>
						<a class="delete-item scheduled-item" href="<?php echo admin_url( "index.php?action=delete_job&id=$name&sig=$sig&next_run=$next_time" ); ?>"><?php _e( "Delete", 'yith-woocommerce-quick-export' ); ?></a>
					</td>
				</tr>
			<?php
			}
		}
	}
	?>
	</tbody>
</table>