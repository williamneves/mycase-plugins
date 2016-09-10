<div class="pys-box">
  <div class="pys-col pys-col-full">
    <h2 class="section-title">Active Events</h2>
   
    <div class="tablenav top">
      <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=pys_edit_std_event" class="button button-primary action thickbox">Add new event</a>
      <a href="#" class="button btn-delete-std-events action">Delete selected</a>
    </div>
  
    <table class="widefat fixed pys-list pys-std-events-list">
      <thead>
        <tr>
          <td class="check-column"><input type="checkbox"></td>
          <th scope="col" class="column-type">Type</th>
          <th scope="col" class="column-url">URL</th>
          <th scope="col" class="column-code">Code</th>
          <th scope="col" class="column-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        
        <?php if( $std_events = get_option( 'pixel_your_site_std_events' ) ) : ?>
        
          <?php foreach( $std_events as $key => $params ) : ?>

		        <?php

		        // skip wrong events
		        if( ! isset( $params['eventtype'] ) || ! isset( $params['pageurl'] ) ) {
			        continue;
		        }

		        ?>
          
          <tr>
            <th scope="row" class="check-column">
              <input type="checkbox" class="std-event-check" data-id="<?php echo $key; ?>">
            </th>
            
            <td><?php echo $params['eventtype']; ?></td>
            <td><pre><?php echo $params['pageurl']; ?></pre></td>
            <td>
            <?php
            
              $code = '';
              if( $params['eventtype'] == 'CustomCode' ) {
                
                $code = $params['code'];
                
              } else {

				$event_type = $params['eventtype'];
				$params = pys_clean_system_event_params( $params );
				$code = pys_build_event_pixel_code( $params, $event_type );
				$code = $code['js'];
                
              }
              
              $code = stripcslashes( $code );
              $code = trim( $code );
              echo '<pre>' . $code . '</pre>'; 
              
            ?>
            </td>
            <td>
              <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=pys_edit_std_event&id=<?php echo $key; ?>" class="button action thickbox">Edit</a>
              <a href="#" class="button btn-delete-std-event action" data-id="<?php echo $key; ?>">Delete</a>
            </td>
          </tr>
          
          <?php endforeach; ?>
        
        <?php endif; ?>
        
      </tbody>
    </table>
  </div>
</div>