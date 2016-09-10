<div class="pys-box">
  <div class="pys-col pys-col-full">
    <h2>General Event (optional)</h2>
    <p>This event can be very useful for building Custom Audiences based on Custom Combination.</p>
    
    <table class="layout">
      <tr class="tall">
        <td class="alignright"><p class="label">Enable general event setup</p></td>
        <td>
          <input type="checkbox" name="pys[general][general_event_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'general_event_enabled' ); ?> >
        </td>
      </tr>

	    <tr class="tall">
		    <td></td>
		    <td>
			    <input type="checkbox" name="" value="1" class="disabled">Track tags - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a>
			    <span class="help">Will pull <code>tags</code> param on posts and custom post types</span>
		    </td>
	    </tr>
      
      <tr>
        <td class="alignright"><p class="label">General event name</p></td>
        <td>
          <input type="text" name="pys[general][general_event_name]"  
            value="<?php echo pys_get_option( 'general', 'general_event_name' ); ?>">
        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" name="pys[general][general_event_on_posts_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'general_event_on_posts_enabled' ); ?> >Enable on Posts</input>
          <span class="help">Will pust post title as <code>content_name</code> and post category name as <code>category_name</code></span>
        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" name="pys[general][general_event_on_pages_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'general_event_on_pages_enabled' ); ?> >Enable on Pages</input>
          <span class="help">Will pull page title as <code>content_name</code></span>
        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" name="pys[general][general_event_on_tax_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'general_event_on_tax_enabled' ); ?> >Enable on Taxonomies</input>
          <span class="help">Will pull taxonomy name as <code>content_name</code></span>
        </td>
      </tr>
      
      <?php if( pys_is_edd_active() ) : ?>
      <tr>
        <td></td>
        <td>
          <input type="checkbox" name="pys[general][general_event_on_edd_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'general_event_on_edd_enabled' ); ?> >Enable on Easy Digital Downloads Products</input>
          <span class="help">Will pull product title as <code>content_name</code> and product category name as <code>category_name</code>, product price as <code>value</code>, currency as <code>currency</code>, post type as <code>content_type</code>.</span>
        </td>
      </tr>
      <?php endif; ?>
      
      <?php
      
      // Display settings for all custom post types
      foreach( get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' ) as $pt ) :
        
        // skip product post type when woo is active
        if( pys_is_woocommerce_active() && $pt->name == 'product' )
          continue;
          
        // skip download post type when eedd is active
        if( pys_is_edd_active() && $pt->name == 'download' )
          continue;
          
      ?>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" name="pys[general][general_event_on_<?php echo $pt->name; ?>_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'general_event_on_' . $pt->name . '_enabled' ); ?>
          >Enable on <?php echo $pt->label; ?> Post Type</input>
          <span class="help">Will pull <?php echo $pt->name; ?> title as <code>content_name</code> and <?php echo $pt->name; ?> category name as <code>category_name</code>, <code>content_type</code> as <code><?php echo $pt->name; ?></code>.</span>
        </td>
      </tr>
      
      
      <?php endforeach; ?>
      
    </table>
    
    <p>The General Event can help you create Super Powerful Custom Audiences, so we made a guide about how to use it: <a href="http://www.pixelyoursite.com/general-event" target="_blank">Click Here to Download the Guide For Free</a></p>
    
    <hr>

	  <h2>TimeOnPage Event</h2>
	  <p>TimeOnPage event will pull the time spent on each page in seconds, the  page name as <code>content_name</code>, and the page ID as <code>content_ids</code> - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></p>

	  <table class="layout disabled">

		  <tr>
			  <td class="alignright"><p class="label">Enable TimeOnPage event setup</p></td>
			  <td>
				  <input type="checkbox" name="pys[general][timeonpage_enabled]" value="1"
					  <?php echo pys_checkbox_state( 'general', 'timeonpage_enabled' ); ?> >
			  </td>
		  </tr>

	  </table>

	  <p>You can find more details on this event on <a href="http://www.pixelyoursite.com/facebook-pixel-plugin-help" target="_blank">our help page</a></p>

	  <hr>
    
    <h2>Search Event</h2>
    <p>The Search event will be active on Search page and will automatically pull search string as parameter. Useful for creating Custom Audiences.</p>
    
    <table class="layout">
      
      <tr>
        <td class="alignright"><p class="label">Enable Search event setup</p></td>
        <td>
          <input type="checkbox" name="pys[general][search_event_enabled]" value="1"
            <?php echo pys_checkbox_state( 'general', 'search_event_enabled' ); ?> >
        </td>
      </tr>
      
    </table>
    
    <hr>
    
    <table class="layout">
      
      <tr>
        <td class="alignright"><p class="label">Remove Pixel for:</p></td>
        <td></td>
      </tr>
      
      <?php
      
      /**
       * List all available roles
       */ 
      
      global $wp_roles;
      
      if( !isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
      }
      
      $roles = $wp_roles->get_names();
      foreach( $roles as $role_value => $role_name ) : ?>
      
      <tr>
        <td class="alignright"><?php echo $role_name; ?></td>
        <td>
          <input type="checkbox" name="pys[general][disable_for_<?php echo $role_value; ?>]" value="1"
            <?php echo pys_checkbox_state( 'general', 'disable_for_' . $role_value ); ?> >
        </td>
      </tr>
        
      <?php endforeach; ?>

	    <tr>
		    <td class="alignright">Guest</td>
		    <td>
			    <input type="checkbox" name="pys[general][disable_for_guest]" value="1"
				    <?php echo pys_checkbox_state( 'general', 'disable_for_guest' ); ?> >
		    </td>
	    </tr>
      
    </table>
    
  </div>
</div>