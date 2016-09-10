<div class="pys-box">
  <div class="pys-col pys-col-full">
    <!--<h2 class="section-title">On Page/Post Events</h2>-->
    <!--<p>Add standard or custom events that will trigger when a URL loads.</p>-->

    <table class="layout">
      <tr>
        <td class="alignright">
          <p class="label big">Activate Events</p>
        </td>
        <td>
          <input type="checkbox" name="pys[std][enabled]" value="1"
            <?php echo pys_checkbox_state( 'std', 'enabled' ); ?> ></input>
        </td>
      </tr>
    </table>
    
    <button class="pys-btn pys-btn-blue pys-btn-big aligncenter">Save Settings</button>
    
  </div>
</div>