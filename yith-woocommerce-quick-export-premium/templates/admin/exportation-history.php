<?php
$scheduled_jobs = get_option( 'ywqe_job_history', array() );

?>
<h2><?php _e( 'Processed task list', 'yith-woocommerce-quick-export' ); ?>
</h2>

<form id="job-scheduled" method="post">
	<div class="alignleft actions bulkactions">
		<label for="bulk-action-selector-top"
		       class="screen-reader-text"><?php _e( "Select bulk action", 'yith-woocommerce-quick-export' ); ?></label>
		<select name="job_action">
			<option name="bulk-actions" value="-1"><?php _e( "Bulk Actions", 'yith-woocommerce-quick-export' ); ?></option>
			<option name="move-to-bin" value="trash"><?php _e( "Move to Bin", 'yith-woocommerce-quick-export' ); ?></option>
		</select>
		<input type="submit" id="doaction" class="button action" value="<?php _e( "Apply", 'yith-woocommerce-quick-export' ); ?>">
	</div>

	<table class="widefat">
		<thead>
		<tr>
			<th><input id="cb-select" type="checkbox"></th>
			<th><?php _e( 'ID', 'yith-woocommerce-quick-export' ); ?></th>
			<th><?php _e( 'Name', 'yith-woocommerce-quick-export' ); ?></th>
			<th><?php _e( 'Exportation time', 'yith-woocommerce-quick-export' ); ?></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<?php

		foreach ( $scheduled_jobs as $key => $job ):

			//$job = maybe_unserialize( $job );
			?>

			<tr id="scheduled-job-<?php echo $job["job_id"]; ?>">
				<td><input id="cb-select-<?php echo $job["job_id"]; ?>" type="checkbox" name="job_list[]"
				           value="<?php echo $job["file_id"]; ?>"></td>
				<td><?php echo $job["job_id"]; ?></td>
				<td><?php echo $job["name"]; ?></td>
				<td><?php echo $job["generation_date"]; ?></td>
				<td>
					<a class="download-item archive-item"
					   href="<?php echo admin_url( "index.php?action=download_item&item_id={$job["file_id"]}" ); ?>"><?php _e( "Download", 'yith-woocommerce-quick-export' ); ?></a>
					&nbsp;
					<a class="delete-item archive-item"
					   href="<?php echo admin_url( "index.php?action=delete_history_item&item_id={$job["file_id"]}" ); ?>"><?php _e( "Delete", 'yith-woocommerce-quick-export' ); ?></a>
				</td>
			</tr>
		<?php endforeach; ?>

		</tbody>
	</table>
</form>