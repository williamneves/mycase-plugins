<div class="wrap">
    <h2><?php _e('Carts', 'yith-woocommerce-recover-abandoned-cart') ?></h2>

	<div id="poststuff">
		<div id="post-body" class="metabox-holder">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<form method="post">
						<input type="hidden" name="page" value="yith_woocommerce_recover_abandoned_cart" />
						<?php $this->cpt_obj->search_box('search', 'search_id'); ?>
					</form>
					<form method="post">
						<?php
						$this->cpt_obj->prepare_items();
						$this->cpt_obj->display(); ?>
					</form>
				</div>
			</div>
		</div>
		<br class="clear">
	</div>
</div>