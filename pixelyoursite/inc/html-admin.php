<?php

// set active tab
$pys_active_tab = 'general';
if( isset( $_REQUEST['active_tab'] ) ) {
	$pys_active_tab = $_REQUEST['active_tab'];
}

$std_events = get_option( 'pixel_your_site_std_events' );

?>

<div class="wrap">
	<div class="pys-logo"></div>
	<h1>Manage your Facebook Pixel</h1>

	<div class="pys-body">

		<ul class="pys-menu">
			<li id="pys-menu-general" class="nav-tab <?php echo $pys_active_tab == 'general' ? 'nav-tab-active selected' : null; ?>">Facebook Pixel</li>
			<li id="pys-menu-posts-events" class="nav-tab <?php echo $pys_active_tab == 'posts-events' ? 'nav-tab-active selected' : null; ?>">Events</li>
			<li id="pys-menu-dynamic-events" class="nav-tab <?php echo $pys_active_tab == 'dynamic-events' ? 'nav-tab-active selected' : null; ?>">Dynamic Events</li>
			<li id="pys-menu-woo" class="nav-tab <?php echo $pys_active_tab == 'woo' ? 'nav-tab-active selected' : null; ?>">WooCommerce Setup</li>
		</ul>
		
		<div class="pys-content">
			<form action="<?php echo admin_url('admin.php'); ?>?page=pixel-your-site" method="post">
				<input type="hidden" name="active_tab" value="<?php echo $pys_active_tab; ?>">

				<?php wp_nonce_field( 'pys-nonce-action', 'pysnonce' ); ?>

				<div id="pys-panel-general" class="pys-panel" <?php echo $pys_active_tab == 'general' ? 'style="display: block;"' : null; ?> >
					
					<?php include "html-box-top-general.php"; ?>
					
					<?php include "html-tab-pixel-id.php"; ?>
					<?php include "html-tab-pixel-general.php"; ?>
					<?php include "html-box-middle.php"; ?>
					<?php include "html-tab-pixel-activate.php"; ?>
					
				</div><!-- #pys-panel-general -->
							
				<div id="pys-panel-posts-events" class="pys-panel" <?php echo $pys_active_tab == 'posts-events' ? 'style="display: block;"' : null; ?> >
					
					<?php include "html-box-top-post-event.php"; ?>
					
					<?php include "html-tab-std-add-event.php"; ?>
					<?php include "html-tab-std-event-general.php"; ?>
					<?php include "html-tab-std-event-list.php"; ?>
					
					<?php include "html-box-middle.php"; ?>
					
				</div><!-- #pys-panel-posts-events -->
		
				<div id="pys-panel-dynamic-events" class="pys-panel" <?php echo $pys_active_tab == 'dynamic-events' ? 'style="display: block;"' : null; ?> >
					
					<?php include "html-box-top-dynamic.php"; ?>
					
					<?php include "html-tab-dynamic-events-general.php"; ?>
					<?php include "html-tab-dynamic-events-list.php"; ?>

					<?php include "html-box-middle.php"; ?>
					
				</div><!-- #pys-panel-dynamic-events -->

				<div id="pys-panel-woo" class="pys-panel" <?php echo $pys_active_tab == 'woo' ? 'style="display: block;"' : null; ?> >
					
					<?php include "html-box-top-woo.php"; ?>
					
					<?php if( pys_is_woocommerce_active() ): ?>
					
					<?php include "html-tab-woo-general.php"; ?>

					<?php include "html-tab-woo-red.php"; ?>
					
					<?php else: ?>
					
					<div class="pys-box pys-box-red">
						<h2 style="text-align: center; color: #fff;">Please install and activate WooCommerce to enable WooCommerce integration.</h2>
					</div>
					
					<?php endif; ?>

				</div><!-- #pys-panel-woo -->

			</form>
		</div><!-- .pys-content -->
		
		<?php include "html-box-bottom.php"; ?>
		
		<p class="pys-rating">If you find PixelYourSite helpful <a href="https://wordpress.org/support/view/plugin-reviews/pixelyoursite?rate=5#postform" target="_blank">click here to give us a 5 stars review</a>, because it will really help us. Thank You!</p>
		
	</div><!-- .pys-body -->
</div><!-- .wrap -->