<?php

/**
 * Show Std Event edit modal content.
 */
if( !function_exists( 'pys_edit_std_event' ) ) {

	add_action( 'wp_ajax_pys_edit_std_event', 'pys_edit_std_event' );
	function pys_edit_std_event() {

		$id = isset( $_REQUEST['id'] ) == true ? $_REQUEST['id'] : uniqid();

		// get event data
		$events    = (array) get_option( 'pixel_your_site_std_events' );
		$std_event = $events[ $id ];

		?>

		<form action="" method="post" id="std-event-form">
			<input type="hidden" name="action" value="add_std_event">
			<input type="hidden" name="std_event[id]" value="<?php echo $id; ?>">

			<table class="layout <?php echo $std_event['eventtype']; ?>">
				<tr>
					<td class="legend"><p class="label">URL:</p></td>
					<td>
						<input type="text" name="std_event[pageurl]" value="<?php echo $std_event['pageurl']; ?>" id="std-url">
						<span class="help">Event will trigger when this URL is visited.<br>If you add * at the end of the URL string, it will match all URLs starting with the this string.</span>
					</td>
				</tr>

				<tr>
					<td class="legend"><p class="label">Event type:</p></td>
					<td>
						<select name="std_event[eventtype]" autocomplete="off" id="std-event-type">
							<?php echo pys_event_types_select_options( $std_event['eventtype'] ); ?>
						</select>
					</td>
				</tr>

				<tr class="ViewContent-visible Search-visible AddToCart-visible AddToWishlist-visible InitiateCheckout-visible AddPaymentInfo-visible Purchase-visible Lead-visible CompleteRegistration-visible">
					<td class="legend"><p class="label">Value:</p></td>
					<td>
						<input type="text" name="std_event[value]" value="<?php echo $std_event['value']; ?>">
						<span class="help">Mandatory for purchase event only.</span>
					</td>
				</tr>

				<tr class="ViewContent-visible Search-visible AddToCart-visible AddToWishlist-visible InitiateCheckout-visible AddPaymentInfo-visible Purchase-visible Lead-visible CompleteRegistration-visible">
					<td class="legend"><p class="label">Currency:</p></td>
					<td>
						<select name="std_event[currency]">
							<option value="">Select Currency</option>
							<?php echo pys_currency_options( $std_event['currency'] ); ?>
						</select>
						<span class="help">Mandatory for purchase event only.</span>
					</td>
				</tr>

				<tr class="ViewContent-visible AddToCart-visible AddToWishlist-visible InitiateCheckout-visible Purchase-visible Lead-visible CompleteRegistration-visible">
					<td class="legend"><p class="label">content_name:</p></td>
					<td>
						<input type="text" name="std_event[content_name]"
						       value="<?php echo $std_event['content_name']; ?>">
						<span class="help">Name of the page/product i.e 'Really Fast Running Shoes'.</span>
					</td>
				</tr>

				<tr class="ViewContent-visible Search-visible AddToCart-visible AddToWishlist-visible InitiateCheckout-visible AddPaymentInfo-visible Purchase-visible">
					<td class="legend"><p class="label">content_ids:</p></td>
					<td>
						<input type="text" name="std_event[content_ids]"
						       value="<?php echo $std_event['content_ids']; ?>">
						<span class="help">Product ids/SKUs associated with the event.</span>
					</td>
				</tr>

				<tr class="ViewContent-visible AddToCart-visible InitiateCheckout-visible Purchase-visible">
					<td class="legend"><p class="label">content_type:</p></td>
					<td>
						<input type="text" name="std_event[content_type]"
						       value="<?php echo $std_event['content_type']; ?>">
						<span class="help">The type of content. i.e product or product_group.</span>
					</td>
				</tr>

				<tr class="Search-visible AddToWishlist-visible InitiateCheckout-visible AddPaymentInfo-visible Lead-visible">
					<td class="legend"><p class="label">content_category:</p></td>
					<td>
						<input type="text" name="std_event[content_category]"
						       value="<?php echo $std_event['content_category']; ?>">
						<span class="help">Category of the page/product.</span>
					</td>
				</tr>

				<tr class="InitiateCheckout-visible Purchase-visible">
					<td class="legend"><p class="label">num_items:</p></td>
					<td>
						<input type="text" name="std_event[num_items]" value="<?php echo $std_event['num_items']; ?>">
						<span class="help">The number of items in the cart. i.e '3'.</span>
					</td>
				</tr>

				<tr class="Purchase-visible">
					<td class="legend"><p class="label">order_id:</p></td>
					<td>
						<input type="text" name="std_event[order_id]" value="<?php echo $std_event['order_id']; ?>">
						<span class="help">The unique order id of the successful purchase. i.e 19.</span>
					</td>
				</tr>

				<tr class="Search-visible">
					<td class="legend"><p class="label">search_string:</p></td>
					<td>
						<input type="text" name="std_event[search_string]"
						       value="<?php echo $std_event['search_string']; ?>">
						<span class="help">The string entered by the user for the search. i.e 'Shoes'.</span>
					</td>
				</tr>

				<tr class="CompleteRegistration-visible">
					<td class="legend"><p class="label">status:</p></td>
					<td>
						<input type="text" name="std_event[status]" value="<?php echo $std_event['status']; ?>">
						<span class="help">The status of the registration. i.e completed.</span>
					</td>
				</tr>

				<tr class="CustomCode-visible">
					<td class="legend"><p class="label" style="line-height: inherit;">Custom event code (advanced users
							only):</p></td>
					<td>
						<textarea name="std_event[code]" rows="5"
						          style="width:100%;"><?php echo stripslashes( $std_event['code'] ); ?></textarea>
						<span class="help">The code inserted in the field MUST be complete, including <code>fbq('track',
								'AddToCart', { … });</code></span>
					</td>
				</tr>

				<tr class="CustomEvent-visible">
					<td class="legend"></td>
					<td>
						<a href="#" class="button button-add-row button-primary action">Add Param</a>
					</td>
				</tr>

			</table>

			<div class="actions-row">
				<a href="#" class="button button-close action">Cancel</a>
				<a href="#" class="button button-save button-primary action disabled"><?php echo isset( $_REQUEST['id'] ) == true ? 'Save' : 'Add'; ?></a>
			</div>

		</form>

		<script>
			jQuery(function ($) {

				validate();

				/* Standard event fields show/hide on event type change. */
				$('#std-event-type').on('change', function () {
					var wrapper = $(this).closest('table');

					wrapper.removeClass();	// clear all classes
					wrapper.addClass('layout');
					wrapper.addClass(this.value);

					validate();

				});

				/* Close modal window */
				$('.button-close').on('click', function (e) {
					e.preventDefault();
					tb_remove();
				});

				/* Save / Add event */
				$('.button-save').on('click', function (e) {
					e.preventDefault();

					if( validate() == false ) {
						return;
					}

					$('#std-event-form').addClass('disabled');
					$(this).text('Saving...');

					var data = $('#std-event-form').serialize();
					data = data + '&action=pys_update_std_event';

					$.ajax({
						url: '<?php echo admin_url('admin-ajax.php'); ?>',
						type: 'post',
						dataType: 'json',
						data: data
					})
						.done(function (data) {

							$("input[name='active_tab']").val('posts-events');
							$('.pys-content > form').submit();
							//location.reload(true);

						});

				});

				// Form validation
				$('form').submit(function(e) {

					if( validate() == false ) {
						e.preventDefault();
					}

				});

				$('#std-url').on( 'change, keyup', function(e){
					validate();
				});

				function validate() {

					var pageURL = $('#std-url').val(),
						eventType = $('#std-event-type').val(),
						btnSave = $('.button-save'),
						isValid = true;

					if( eventType == null || pageURL.length == 0 ) {
						isValid = false;
					}

					if( isValid ) {
						btnSave.removeClass('disabled');
					} else {
						btnSave.addClass('disabled');
					}

					return isValid;

				}

			});
		</script>

		<?php
		exit();
	}

}

/**
 * Update or Add Std Event.
 */
if( !function_exists( 'pys_update_std_event' ) ) {

	add_action( 'wp_ajax_pys_update_std_event', 'pys_update_std_event' );
	function pys_update_std_event() {

		$events    = (array) get_option( 'pixel_your_site_std_events' );
		$std_event = $_REQUEST['std_event'];

		$id = $std_event['id'];
		unset( $std_event['action'] );
		unset( $std_event['id'] );

		unset( $std_event['custom_names'] );
		unset( $std_event['custom_values'] );

		$events[ $id ] = $std_event;
		update_option( 'pixel_your_site_std_events', $events );

		echo json_encode( 'success' );

		exit();
	}

}

/**
 * Delete Std Event(s).
 */
if( !function_exists( 'pys_delete_std_event' ) ) {

	add_action( 'wp_ajax_pys_delete_std_event', 'pys_delete_std_event' );
	function pys_delete_std_event() {

		$events = (array) get_option( 'pixel_your_site_std_events' );
		$ids    = $_REQUEST['ids'];

		// remove requested ids
		foreach ( $ids as $id ) {

			unset( $events[ $id ] );

		}

		update_option( 'pixel_your_site_std_events', $events );

		echo json_encode( 'success' );
		exit();
	}

}