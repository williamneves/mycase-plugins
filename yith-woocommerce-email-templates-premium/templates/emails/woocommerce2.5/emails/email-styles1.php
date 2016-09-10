<?php
/**
 * Email Styles 1
 */

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * @var WC_Email $current_email
 */
global $current_email;

$template = yith_wcet_get_email_template( $current_email );

$meta = get_post_meta( $template, '_template_meta', true );

$bg        = ( isset( $meta[ 'bg_color' ] ) ) ? $meta[ 'bg_color' ] : '#F5F5F5';
$body      = ( isset( $meta[ 'body_color' ] ) ) ? $meta[ 'body_color' ] : '#FFFFFF';
$base      = ( isset( $meta[ 'base_color' ] ) ) ? $meta[ 'base_color' ] : '#2470FF';
$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );
$text      = ( isset( $meta[ 'txt_color' ] ) ) ? $meta[ 'txt_color' ] : '#000000';

// PREMIUM
$page_width               = ( isset( $meta[ 'page_width' ] ) ) ? $meta[ 'page_width' ] . 'px' : '800px';
$logo_height              = ( isset( $meta[ 'logo_height' ] ) ) ? $meta[ 'logo_height' ] . 'px' : '100px';
$page_border_radius       = ( isset( $meta[ 'page_border_radius' ] ) ) ? $meta[ 'page_border_radius' ] . 'px' : '3px';
$header_position          = ( isset( $meta[ 'header_position' ] ) ) ? $meta[ 'header_position' ] : 'center';
$header_color             = ( isset( $meta[ 'header_color' ] ) ) ? $meta[ 'header_color' ] : $body;
$h1_size                  = ( isset( $meta[ 'h1_size' ] ) ) ? $meta[ 'h1_size' ] . 'px' : '30px';
$h2_size                  = ( isset( $meta[ 'h2_size' ] ) ) ? $meta[ 'h2_size' ] . 'px' : '18px';
$h3_size                  = ( isset( $meta[ 'h3_size' ] ) ) ? $meta[ 'h3_size' ] . 'px' : '16px';
$body_size                = ( isset( $meta[ 'body_size' ] ) ) ? $meta[ 'body_size' ] . 'px' : '14px';
$body_line_height         = ( isset( $meta[ 'body_line_height' ] ) ) ? $meta[ 'body_line_height' ] . 'px' : '20px';
$table_border_width       = ( isset( $meta[ 'table_border_width' ] ) ) ? $meta[ 'table_border_width' ] . 'px' : '1px';
$table_border_width_plus2 = ( isset( $meta[ 'table_border_width' ] ) ) ? ( intval( $meta[ 'table_border_width' ] ) + 2 ) . 'px' : '2px';
$table_border_color       = ( isset( $meta[ 'table_border_color' ] ) ) ? $meta[ 'table_border_color' ] : '#cccccc';
$price_title_bg_color     = ( isset( $meta[ 'price_title_bg_color' ] ) ) ? $meta[ 'price_title_bg_color' ] : '#ffffff';
$table_bg_color           = ( isset( $meta[ 'table_bg_color' ] ) ) ? $meta[ 'table_bg_color' ] : 'transparent';
$footer_text_color        = ( isset( $meta[ 'footer_text_color' ] ) ) ? $meta[ 'footer_text_color' ] : '#555555';

// --------- Header Padding -----------
$header_padding_default = array( 36, 48, 36, 48 );
$header_padding         = ( isset( $meta[ 'header_padding' ] ) ) ? $meta[ 'header_padding' ] : $header_padding_default;
if ( !is_array( $header_padding ) || count( $header_padding ) != 4 ) {
    $header_padding = $header_padding_default;
}
$header_padding_css = '';
foreach ( $header_padding as $h_pad ) {
    $header_padding_css .= intval( $h_pad ) . 'px ';
}
// ------ Header Padding END ----------

$bg_darker_10    = wc_hex_darker( $bg, 10 );
$base_lighter_20 = wc_hex_lighter( $base, 20 );
$text_lighter_20 = wc_hex_lighter( $text, 20 );

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
?>
#wrapper {
background-color: <?php echo esc_attr( $bg ); ?>;
margin: 0;
padding: 70px 0 70px 0;
-webkit-text-size-adjust: none !important;
width: 100%;
}

#template_container {
border-radius: <?php echo esc_attr( $page_border_radius ); ?> !important;
width: <?php echo $page_width; ?>;
overflow: hidden;
}

#template_header_image {
background-color: <?php echo esc_attr( $header_color ); ?>;
color: <?php echo $base_text; ?>;
border-bottom: 0;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-weight: bold;
line-height: 100%;
vertical-align: middle;
text-align: <?php echo $header_position ?>;
width: <?php echo $page_width ?>;
}

#template_header_image img{
height:<?php echo $logo_height; ?>;
margin-top: 10px;
margin-bottom :10px;
}

#template_header {
background-color: <?php echo esc_attr( $base ); ?>;
color: <?php echo esc_attr( $base_text ); ?>;
border-bottom: 0;
font-weight: bold;
line-height: 100%;
vertical-align: middle;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
width: <?php echo $page_width; ?>;
}

#template_header h1 {
color: <?php echo esc_attr( $base_text ); ?>;
}

#template_custom_links {
background: <?php echo esc_attr( $body ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
vertical-align: middle;
width: <?php echo $page_width; ?>;
text-align: left;
height: 45px;
margin-bottom: 5px;
}
#template_custom_links a{
color: <?php echo esc_attr( $text ); ?>;
font-size: 12px;
text-decoration: none;
text-transform: uppercase;
font-weight: 600;
}
#template_custom_links{
padding: 0px 20px;
}
#template_custom_links td{
padding: 0 0px;
}
#template_custom_links ul{
margin: 5px 0;
}
#template_custom_links li{
display: inline-block;
padding: 0 10px;
vertical-align: middle;
}

#template_custom_links li.yith-wcet-socials-icons{
height: 20px;
width: 20px;
padding: 0 5px;
float:right;
}

.yith-wcet-socials-icons img{
width:30px;
height:30px;
}

#template_body {
width: <?php echo $page_width; ?>;
}

th#yith-wcet-th-title-price{
background: <?php echo $price_title_bg_color; ?>;
}

#template_footer {
width: <?php echo $page_width; ?>;
background: <?php echo esc_attr( $body ); ?>;
margin-top: 5px;
padding-right: 20px;
}

#template_footer img{
height:70px;
vertical-align: middle;
margin-left: 20px;
}

#template_footer td {

}

#template_footer #credit {
padding: 0 48px 48px 48px;
}

#body_content {
background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
padding: 30px;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#body_content table th {
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#body_content table td td {
padding: 12px;
}

#body_content table td th {
padding: 12px;
}

#body_content p {
margin: 0 0 16px;
}

#body_content_inner {
color: <?php echo esc_attr( $text_lighter_20 ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: <?php echo esc_attr( $body_size ) ?>;
line-height: <?php echo esc_attr( $body_line_height ) ?>;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#header_wrapper {
padding: <?php echo $header_padding_css; ?>;
display: block;
}

h1 {
color: <?php echo esc_attr( $base ); ?>;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: <?php echo esc_attr( $h1_size ) ?>;
font-weight: 300;
line-height: 150%;
margin: 0;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
text-shadow: 0 1px 0 <?php echo esc_attr( $base_lighter_20 ); ?>;
-webkit-font-smoothing: antialiased;
}

h2 {
color: <?php echo esc_attr( $text ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: <?php echo esc_attr( $h2_size ) ?>;
font-weight: bold;
line-height: 130%;
margin: 16px 0 8px;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
color: <?php echo esc_attr( $text ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: <?php echo esc_attr( $h3_size ) ?>;
font-weight: bold;
line-height: 130%;
margin: 16px 0 8px;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

a {
color: <?php echo esc_attr( $base ); ?>;
text-decoration: none;
}

img {
border: none;
display: inline;
font-size: 14px;
font-weight: bold;
height: auto;
line-height: 100%;
outline: none;
text-decoration: none;
text-transform: capitalize;
}

#yith-wcet-order-items-table{
border: <?php echo esc_attr( $table_border_width ); ?> solid <?php echo esc_attr( $table_border_color ); ?>;
background: <?php echo esc_attr( $table_bg_color ); ?>;
border-collapse: collapse;
border-left: none;
border-right: none;
}

.yith-wcet-order-items-table-element{
border: <?php echo esc_attr( $table_border_width ); ?> solid <?php echo esc_attr( $table_border_color ); ?>;
border-collapse: collapse;
border-spacing: 0;
border-left: none;
border-right: none;
}

thead th.yith-wcet-order-items-table-element{
font-size: 11px;
text-transform: uppercase;
}

tfoot .yith-wcet-order-items-table-element{
border: none !important;
}

#template_footer_social{
background: <?php echo esc_attr( $body ); ?>;
height:35px;
padding:20px 0;
}

#template_footer_text{
color: <?php echo esc_attr( $footer_text_color ); ?>;
font-family: Helvetica Neue,Helvetica,Roboto,Arial,sans-serif;
font-size: 13px;
padding: 0 20px;
text-align: right;
}

#template_footer_wc_credits{
color: #565656;
font-family: Helvetica Neue,Helvetica,Roboto,Arial,sans-serif;
font-size: 13px;
padding:20px 0;
}


th#yith-wcet-th-title-product{
width:50%;
}
th#yith-wcet-th-title-quantity{
width:20%;
text-align: center;
}
th#yith-wcet-th-title-price{
width:30%;
text-align: center;
}

tfoot th{
text-align: right;
text-transform: none;
font-size: 11px;
}

tfoot td{
text-align: right;
}

.yith-wcet-order-items-table-element-quantity{
border-bottom:<?php echo esc_attr( $table_border_width ); ?> solid <?php echo esc_attr( $table_border_color ); ?>;
border-collapse: collapse;
border-spacing: 0;
text-align: center;
}
.yith-wcet-order-items-table-element-price{
border-bottom:<?php echo esc_attr( $table_border_width ); ?> solid <?php echo esc_attr( $table_border_color ); ?>;
border-collapse: collapse;
border-spacing: 0;
text-align: center;
}

#template_footer_extra_text{
color: <?php echo esc_attr( $footer_text_color ); ?>;
font-family: Helvetica Neue,Helvetica,Roboto,Arial,sans-serif;
font-size: 13px;
padding: 0 20px;
text-align: center;
}

.ywrr-unsubscribe-link{
text-decoration: underline;
color: <?php echo esc_attr( $base ); ?>;
font-weight: bold;
}

.ywrr-items{
display: block;
padding: 20px 0;
color:<?php echo esc_attr( $base ); ?>;
height: 135px;
font-size: <?php echo esc_attr( $h3_size ); ?>;
font-weight: bold;
text-decoration: none;
border-bottom: 1px solid <?php echo esc_attr( $table_border_color ); ?>;
}

.ywrr-items .ywrr-items-image{
display: block;
float:left;
height: 135px;
margin-right: 20px;
}

.ywrr-items .ywrr-items-title{
display: block;
margin: 25px 0 0 0;
}

<?php if ( defined( 'YWRR_ASSETS_URL' ) ) : ?>
    .ywrr-items .ywrr-items-vote{
    display: inline-block;
    font-size: 11px;
    color: <?php echo esc_attr( $text ); ?>;
    line-height: 40px;
    text-transform: uppercase;
    background: url(<?php echo untrailingslashit( YWRR_ASSETS_URL ) ?>/images/rating-stars.png) no-repeat bottom left;
    padding: 0 0 22px 0;
    width: 150px;
    }
<?php endif ?>

.ywces-h2 {
color: <?php echo esc_attr( $base ); ?>;
display: block;
font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
font-size: <?php echo esc_attr( $h2_size ) ?>;
font-weight: bold;
line-height: 130%;
margin: 16px 0 0 0;
text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
text-transform: none;
}

.ywces-i {
font-size: small;
margin: 0 0 16px 0;
display: block;
}

.ywces-a {
color: <?php echo esc_attr( $base ); ?>;
}

.ywces-span {
font-size: small;
font-style: italic;
}

<?php
/**
 * Action yith_wcet_after_email_styles
 * Params:
 *      premium_style
 *      template meta
 *      currrent email
 */
$premium_style = 1;
do_action( 'yith_wcet_after_email_styles', $premium_style, $meta, $current_email );
?>
