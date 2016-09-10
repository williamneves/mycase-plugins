<div class="pys-box">
  <div class="pys-col pys-col-full">
    <h2 class="section-title">WooCommerce Pixel Settings</h2>
    <p>Add all necessary events on WooCommerce with just a few clicks. On this tab you will find powerful options to customize the Facebook Pixel for your store.</p>

    <hr>
    <h2 class="section-title">Facebook Dynamic Product Ads Pixel Settings</h2>
    <table class="layout">
      <tr class="tall">
        <td colspan="2" class="narrow">
          <input type="checkbox" class="woo-events-toggle"><strong>Enable Facebook Dynamic Product Ads</strong></input>
          <span class="help">This will automaticaly add ViewContent on product pages, AddToCart on add to cart button click and cart page, InitiateCheckout on checkout page and Purchase on thank you page. The events will have the required <code>content_ids</code> and <code>content_type</code> parameters.</span>
        </td>
      </tr>
      
      <tr>
        <td class="alignright"><p class="label">Content ID:</p></td>
        <td>
          <select name="pys[woo][content_id]" autocomplete="off">
            <option <?php selected( 'id', pys_get_option( 'woo', 'content_id' ) ); ?> value="id">Product ID</option>
            <option <?php selected( 'sku', pys_get_option( 'woo', 'content_id' ) ); ?> value="sku">Product SKU</option>
          </select>        
        </td>
      </tr>
      
      <tr>
        <td class="alignright"><p class="label">Define Variation ID:</p></td>
        <td>
          <select name="pys[woo][variation_id]" autocomplete="off">
            <option <?php selected( 'main', pys_get_option( 'woo', 'variation_id' ) ); ?> value="main">Main product data</option>
            <option <?php selected( 'variation', pys_get_option( 'woo', 'variation_id' ) ); ?> value="variation">Variation data</option>
          </select>
          <span class="help">Define what ID should be use for variations of variable product.</span>
        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <p><strong>Product Catalog Feed</strong> - use our dedicated plugin to create 100% Dynamic Ads compatible feeds with just a few clicks:</p>
          <p><a href="http://www.pixelyoursite.com/product-catalog-facebook" target="_blank">Download Product Catalog Feed Plugin for a big discount</a></p>
        </td>
      </tr>
    </table>
    
    <hr>
    <h2 class="section-title">PRO Options</h2>
    <table class="layout">
      <tr class="disabled" style="vertical-align: top;">
        <td>
          
          <h2>Custom Audiences Optimization</h2>
          
          <input type="checkbox">Enable Additional Parameters</input>
          <span class="help">Product name will be pulled as <code>content_name</code>, and Product Category as <code>category_name</code> for all WooCommerce events.</span>
	        <span class="help" style="margin-bottom: 20px;">The number of items is <code>num_items</code> for InitiateCheckout and Purchase events.</span>

	        <input type="checkbox" name="" disabled>Track tags
	        <span class="help">Will pull <code>tags</code> param on all WooCommerce events.</span>


        </td>
        <td>
          
          <h2>Tax Options</h2>
          Value: &nbsp;&nbsp;
          <select autocomplete="off">
            <option selected>Includes Tax</option>
          </select>

        </td>
      </tr>
      
      <tr  style="vertical-align: top;">
        <td>
          
          <p><strong>Important for Custom Audiences.</strong> Use this together with the General Event option.</p>
          <p>Learn how to <strong>Create Powerful Custom Audiences</strong> based on Events: <strong><a href="http://www.pixelyoursite.com/general-event" target="_blank">Click to Download Your Free Guide</a></strong></p>
          
        </td>
        <td>
          
          <p><strong>Unlock all the PRO features: <a href="http://www.pixelyoursite.com/facebook-pixel-plugin" target="_blank">Upgrade NOW</a></strong></p>

        </td>
      </tr>
      
      
    </table>
    
    <hr>
    <h2 class="section-title">ViewContent Event</h2>
    <p>ViewContent is added on Product Pages and it is required for Facebook Dynamic Product Ads.</p>
    <table class="layout">
      <tr class="tall">
        <td colspan="2" class="narrow">
          <input type="checkbox" name="pys[woo][on_view_content]" value="1" class="woo-option"
            <?php echo pys_checkbox_state( 'woo', 'on_view_content' ); ?> >
            <strong>Enable ViewContent on Product Pages</strong></input>
        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" disabled>Enable Value - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></input>
          <span class="help">add value and currency - Important for ROI measurement</span> 
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label big">Define value:</p></td>
        <td></td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Product price</p></td>
        <td>
          <input type="radio" name="pricea" checked>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Percent of product price</p></td>
        <td>
          <input type="radio" name="pricea">
          <input type="text">%
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Use Global value</p></td>
        <td>
          <input type="radio" name="pricea">
          <input type="text">
        </td>
      </tr>

    </table>
    
    <hr>
    <h2 class="section-title">AddToCart Event</h2>
    <p>AddToCart event will be added  on add to cart button click and on cart page. It is required for Facebook Dynamic Product Ads.</p>
    <table class="layout">
      <tr>
        <td colspan="2" class="narrow">
          
          <input type="checkbox" name="pys[woo][on_add_to_cart_btn]" value="1" class="woo-option"
            <?php echo pys_checkbox_state( 'woo', 'on_add_to_cart_btn' ); ?> >
            <strong>Enable AddToCart on add to cart button</strong></input>

        </td>
      </tr>
      
      <tr class="tall">
        <td colspan="2" class="narrow">
          
          <input type="checkbox" name="pys[woo][on_cart_page]" value="1" class="woo-option"
            <?php echo pys_checkbox_state( 'woo', 'on_cart_page' ); ?> >
            <strong>Enable AddToCart on cart page</strong></input>
            
        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" disabled>Enable Value - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></input>
          <span class="help">add value and currency - Important for ROI measurement</span> 
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label big">Define value:</p></td>
        <td></td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Product price</p></td>
        <td>
          <input type="radio" name="priceb" checked>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Percent of product price</p></td>
        <td>
          <input type="radio" name="priceb">
          <input type="text">%
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Use Global value</p></td>
        <td>
          <input type="radio" name="priceb">
          <input type="text">
        </td>
      </tr>

    </table>
    
    <hr>
    <h2 class="section-title">InitiateCheckout Event</h2>
    <p>InitiateCheckout event will be enabled on the Checkout page. It is not mandatory for Facebook Dynamic Product Ads, but it is better to keep it on.</p>
    <table class="layout">
      
      <tr class="tall">
        <td colspan="2" class="narrow">
          
          <input type="checkbox" name="pys[woo][on_checkout_page]" value="1" class="woo-option"
            <?php echo pys_checkbox_state( 'woo', 'on_checkout_page' ); ?> >
            <strong>Enable InitiateCheckout on Checkout page</strong></input>

        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" disabled>Enable Value - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></input>
          <span class="help">add value and currency - Important for ROI measurement</span> 
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label big">Define value:</p></td>
        <td></td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Products price (subtotal)</p></td>
        <td>
          <input type="radio" name="pricec" checked>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Percent of products value (subtotal)</p></td>
        <td>
          <input type="radio" name="pricec">
          <input type="text">%
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Use Global value</p></td>
        <td>
          <input type="radio" name="pricec">
          <input type="text">
        </td>
      </tr>

    </table>
    
    <hr>
    <h2 class="section-title">Purchase Event</h2>
    <p>Purchase event will be enabled on the Thank You page. It is mandatory for Facebook Dynamic Product Ads.</p>
    <table class="layout">
      
      <tr class="tall">
        <td colspan="2" class="narrow">
          
          <input type="checkbox" name="pys[woo][on_thank_you_page]" value="1" class="woo-option"
            <?php echo pys_checkbox_state( 'woo', 'on_thank_you_page' ); ?> >
            <strong>Enable Purchase event on Thank You page</strong>

        </td>
      </tr>
      
      <tr>
        <td></td>
        <td>
          <input type="checkbox" disabled="disabled">Enable Value - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a>
          <span class="help">add value and currency - <strong>Very important for ROI measurement</strong></span> 
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label big">Define value:</p></td>
        <td></td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Transport is:</p></td>
        <td>
          <select autocomplete="off" disabled>
            <option selected>Included</option>
          </select>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Total</p></td>
        <td>
          <input type="radio" name="priced" checked>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Percent of Total</p></td>
        <td>
          <input type="radio" name="priced" >
          <input type="text">%
        </td>
      </tr>
      
      <tr class="tall disabled">
        <td class="alignright"><p class="label">Use Global value</p></td>
        <td>
          <input type="radio" name="priced">
          <input type="text">
        </td>
      </tr>

	    <tr>
		    <td class="alignright"><p class="label big">Custom Audience Optimization:</p></td>
		    <td>
			    <span class="help"><strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></span>
		    </td>
	    </tr>

	    <tr class="disabled">
		    <td class="alignright"></td>
		    <td>
			    <input type="checkbox" name="" value="1">
			    <strong>Add Town, State and Country parameters</strong>
			    <span class="help">Will pull <code>town</code>, <code>state</code> and <code>country</code></span>

		    </td>
	    </tr>

	    <tr class="disabled">
		    <td></td>
		    <td>
			    <input type="checkbox" name="" value="1">
			    <strong>Add Payment Method parameter</strong>
			    <span class="help">Will pull <code>payment</code></span>

		    </td>
	    </tr>

	    <tr class="disabled">
		    <td></td>
		    <td>
			    <input type="checkbox" name="" value="1">
			    <strong>Add Shipping Method parameter</strong>
			    <span class="help">Will pull <code>shipping</code></span>
		    </td>
	    </tr>

	    <tr class="disabled">
		    <td></td>
		    <td>
			    <input type="checkbox" name="" value="1">
			    <strong>Add Coupons parameter</strong>
			    <span class="help">Will pull <code>coupon_used</code> and <code>coupon_name</code></span>
		    </td>
	    </tr>

    </table>
    
    <p><strong>Important:</strong> For the Purchase Event to work, the client must be redirected on the default WooCommerce Thank You page after payment.</p>
    
    <hr>
    <h2 class="section-title">WooCommerce Affliliate Products Events</h2>
    <p>You can add an event that will trigger each time an affiliate WooCommerce product button is clicked.</p>
    <table class="layout">
      
      <tr class="tall">
        <td colspan="2" class="narrow">
          <input type="checkbox" disabled><strong>Activate WooCommerce Affliliate Products Events - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></strong></input>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Event type:</p></td>
        <td>
          <input type="radio" name="eta" checked>
          <select autocomplete="off" disabled>
            <option selected>Lead</option>
          </select>
        </td>
      </tr>
      
      <tr class="disabled tall">
        <td class="alignright"><p class="label">Name of custom event:</p></td>
        <td>
          <input type="radio" name="eta">
          <input type="text">
          <span class="help">* The Affiliate event will have all the parameters values specific for selected event.</span>
          <span class="help">* The Custom Affiliate event will have value, currency, content_name, content_type, content_ids.</span>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Do not pull event value</p></td>
        <td>
          <input type="radio" name="pricex" checked>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Event Value = Prouct Price</p></td>
        <td>
          <input type="radio" name="pricex">
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Use Global value</p></td>
        <td>
          <input type="radio" name="pricex">
          <input type="text">
          <span class="help">* Set this if you want a unique global value every time affiliate product clicked.</span>
        </td>
      </tr>

    </table>
    
    <hr>
    <h2 class="section-title">WooCommerce PayPal Standard Events</h2>
    <p>You can add an event that will trigger PayPal Standard button click.</p>
    <table class="layout">
      
      <tr class="tall">
        <td colspan="2" class="narrow">
          <input type="checkbox" disabled><strong>Activate PayPal Standard Events - <strong>This is a PRO feature</strong> - <a href="http://www.pixelyoursite.com/facebook-pixel-plugin">Update NOW</a></strong></input>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Event type:</p></td>
        <td>
          <input type="radio" name="etb" checked>
          <select autocomplete="off" disabled>
            <option selected>InitiatePayment</option>
          </select>
        </td>
      </tr>
      
      <tr class="disabled tall">
        <td class="alignright"><p class="label">Name of custom event:</p></td>
        <td>
          <input type="radio" name="etb">
          <input type="text">
          <span class="help">* The PayPal Standard event will have all the parameters values specific for selected event.</span>
          <span class="help">* The Custom Affiliate event will have value, currency, content_type, content_ids.</span>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Do not pull event value</p></td>
        <td>
          <input type="radio" name="price" checked>
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Event Value = Cart Total</p></td>
        <td>
          <input type="radio" name="price">
        </td>
      </tr>
      
      <tr class="disabled">
        <td class="alignright"><p class="label">Use Global value</p></td>
        <td>
          <input type="radio" name="price">
          <input type="text">
          <span class="help">* Set this if you want a unique global value every time affiliate product clicked.</span>
        </td>
      </tr>

    </table>
    
    <hr>
    
    <table class="layout">
      
      <tr class="tall">
        <td colspan="2" class="narrow">
          <p>This plugin can do a lot of useful stuff, but if you want your ads to shine, follow the steps of other experience marketers and upgrade to the Pro version.<br><a href="http://www.pixelyoursite.com/facebook-pixel-plugin" target="_blank"><strong>Click Here for a Super Offer</strong></a></p>
        </td>
      </tr>
      
      <tr>
        <td class="alignright">
          <p class="label big">Activate WooCommerce Pixel Settings</p>
        </td>
        <td>
          <input type="checkbox" name="pys[woo][enabled]" value="1"
            <?php echo pys_checkbox_state( 'woo', 'enabled' ); ?> >
        </td>
      </tr>
      
    </table>

    <button class="pys-btn pys-btn-blue pys-btn-big aligncenter">Save Settings</button>
    
  </div>
</div>