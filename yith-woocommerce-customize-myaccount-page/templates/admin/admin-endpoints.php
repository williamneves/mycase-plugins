<?php
/**
 * MY ACCOUNT ADMIN ENDPOINTS
 */
if ( ! defined( 'YITH_WCMAP' ) ) {
    exit;
} // Exit if accessed directly

?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo $value['name']; ?></label>
    </th>

    <td class="forminp <?php echo $value['id'] ?>_container">
        <p class="description"><?php echo $value['desc'] ?></p>
        <ul class="endpoints">
            <!-- Endpoints -->
            <?php foreach ( $endpoints as $key => $options ) : ?>

                <?php
                /**
                 * This action print a row with endpoint options
                 *
                 * @hooked yith_wcmap_print_endpoint_fields - 10
                 */
                do_action( 'yith_wcmap_endpoint_fields', $key, $options, $value['id'] )
                ?>

            <?php endforeach; ?>
            <!-- Add Endpoint -->
            <li class="add-endpoint">

                <?php
                /**
                 * This action print a row for add endpoint
                 *
                 * @hooked yith_wcmap_add_new_endpoint_form - 10
                 */
                do_action( 'yith_wcmap_add_new_endpoint' )
                ?>

            </li>
        </ul>
        <input type="hidden" class="endpoints-order" name="<?php echo $value['id'] ?>" value="<?php echo implode( ',', array_keys( $endpoints ) ) ?>" />
    </td>
</tr>