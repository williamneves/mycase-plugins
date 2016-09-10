<style>
.section{
    margin-left: -20px;
    margin-right: -20px;
    font-family: "Raleway",san-serif;
}
.section h1{
    text-align: center;
    text-transform: uppercase;
    color: #808a97;
    font-size: 35px;
    font-weight: 700;
    line-height: normal;
    display: inline-block;
    width: 100%;
    margin: 50px 0 0;
}
.section ul{
    list-style-type: disc;
    padding-left: 15px;
}
.section:nth-child(even){
    background-color: #fff;
}
.section:nth-child(odd){
    background-color: #f1f1f1;
}
.section .section-title img{
    display: table-cell;
    vertical-align: middle;
    width: auto;
    margin-right: 15px;
}
.section h2,
.section h3 {
    display: inline-block;
    vertical-align: middle;
    padding: 0;
    font-size: 24px;
    font-weight: 700;
    color: #808a97;
    text-transform: uppercase;
}

.section .section-title h2{
    display: table-cell;
    vertical-align: middle;
    line-height: 25px;
}

.section-title{
    display: table;
}

.section h3 {
    font-size: 14px;
    line-height: 28px;
    margin-bottom: 0;
    display: block;
}

.section p{
    font-size: 13px;
    margin: 25px 0;
}
.section ul li{
    margin-bottom: 4px;
}
.landing-container{
    max-width: 750px;
    margin-left: auto;
    margin-right: auto;
    padding: 50px 0 30px;
}
.landing-container:after{
    display: block;
    clear: both;
    content: '';
}
.landing-container .col-1,
.landing-container .col-2{
    float: left;
    box-sizing: border-box;
    padding: 0 15px;
}
.landing-container .col-1 img{
    width: 100%;
}
.landing-container .col-1{
    width: 55%;
}
.landing-container .col-2{
    width: 45%;
}
.premium-cta{
    background-color: #808a97;
    color: #fff;
    border-radius: 6px;
    padding: 20px 15px;
}
.premium-cta:after{
    content: '';
    display: block;
    clear: both;
}
.premium-cta p{
    margin: 7px 0;
    font-size: 14px;
    font-weight: 500;
    display: inline-block;
    width: 60%;
}
.premium-cta a.button{
    border-radius: 6px;
    height: 60px;
    float: right;
    background: url(<?php echo YITH_YWRAC_URL?>assets/images/upgrade.png) #ff643f no-repeat 13px 13px;
    border-color: #ff643f;
    box-shadow: none;
    outline: none;
    color: #fff;
    position: relative;
    padding: 9px 50px 9px 70px;
}
.premium-cta a.button:hover,
.premium-cta a.button:active,
.premium-cta a.button:focus{
    color: #fff;
    background: url(<?php echo YITH_YWRAC_URL?>assets/images/upgrade.png) #971d00 no-repeat 13px 13px;
    border-color: #971d00;
    box-shadow: none;
    outline: none;
}
.premium-cta a.button:focus{
    top: 1px;
}
.premium-cta a.button span{
    line-height: 13px;
}
.premium-cta a.button .highlight{
    display: block;
    font-size: 20px;
    font-weight: 700;
    line-height: 20px;
}
.premium-cta .highlight{
    text-transform: uppercase;
    background: none;
    font-weight: 800;
    color: #fff;
}

.section.one{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/01-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.two{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/02-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.three{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/03-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.four{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/04-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.five{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/05-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.six{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/06-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.seven{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/07-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.eight{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/08-bg.png) no-repeat #fff; background-position: 85% 75%
}
.section.nine{
    background: url(<?php echo YITH_YWRAC_URL?>/assets/images/09-bg.png) no-repeat #fff; background-position: 85% 75%
}

@media (max-width: 768px) {
    .section{margin: 0}
    .premium-cta p{
        width: 100%;
    }
    .premium-cta{
        text-align: center;
    }
    .premium-cta a.button{
        float: none;
    }
}

@media (max-width: 480px){
    .wrap{
        margin-right: 0;
    }
    .section{
        margin: 0;
    }
    .landing-container .col-1,
    .landing-container .col-2{
        width: 100%;
        padding: 0 15px;
    }
    .section-odd .col-1 {
        float: left;
        margin-right: -100%;
    }
    .section-odd .col-2 {
        float: right;
        margin-top: 65%;
    }
}

@media (max-width: 320px){
    .premium-cta a.button{
        padding: 9px 20px 9px 70px;
    }

    .section .section-title img{
        display: none;
    }
}
</style>
<div class="landing">
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    <?php echo sprintf( __('Upgrade to %1$spremium version%2$s of %1$sYITH WooCommerce Recover Abandoned Cart%2$s to benefit from all features!','ywrac'),'<span class="highlight">','</span>' );?>
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight"><?php _e('UPGRADE','ywrac');?></span>
                    <span><?php _e('to the premium version','ywrac');?></span>
                </a>
            </div>
        </div>
    </div>
    <div class="one section section-even clear">
        <h1><?php _e('Premium Features','ywrac');?></h1>
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/01.png" alt="Type of users" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/01-icon.png" alt="icon 01"/>
                    <h2><?php _e('Type of users','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf(__('Recovering information about a user who has abandoned the cart might turn out to be a very powerful weapon to improve your earnings.%3$sYou can limit your %1$smarketing strategy%2$s to some users only, those who have registered into your site and with a specific %1$srole%2$s, the one you specify from inside the plugin.', 'ywrac'), '<b>', '</b>','<br>');?>
                </p>
            </div>
        </div>
    </div>
    <div class="two section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/02-icon.png" alt="icon 02" />
                    <h2><?php _e('Guest users','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf(__('It happens many times that an %1$sunregistered%2$s user was so near to purchase, had filled our %1$scheckout%2$s form but then had not completed the order for connection problems or for doubts aroused at the last minute.%3$sWith the premium version of the plugin, you will be able to track also these users and contact them in a later moment to remind them of complete their missed purchase.', 'ywrac'), '<b>', '</b>','<br>');?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/02.png" alt="Guest users" />
            </div>
        </div>
    </div>
    <div class="three section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/03.png" alt="Notification email" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/03-icon.png" alt="icon 03" />
                    <h2><?php _e( 'Notification email','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('What\'s more satisfying than seeing an abandoned cart turning into a purchase order? %1$sYou can get emails for each successful conversion%2$s, containing all information about the order and the user who made it. Keep always updated about what happens in your e-commerce shop.', 'ywrac'),'<b>','</b>');?>
                </p>
            </div>
        </div>
    </div>
    <div class="four section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/04-icon.png" alt="icon 04" />
                    <h2><?php _e('Different email templates ','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('Endless email templates that you can configure to encourage reluctant users to %1$scome back to your shop%2$s and evaluate the purchase offer.%3$sWrite emails and schedule their sending, proposing new contents and always more interesting offers. Do not let it go is the first rule to aim to success!', 'ywrac'),'<b>','</b>','<br>');?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/04.png" alt="Email Templates" />
            </div>
        </div>
    </div>
    <div class="five section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/05.png" alt="Product quantity" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/05-icon.png" alt="icon 05" />
                    <h2><?php _e('Coupons','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('The most seducing weapon to approach users that have already visited your shop. A different coupon for each email template: you can configure %1$sdiscounts%2$s and %1$sexpiration date%2$s, in order to urge your users to hurry and reduce the possible useless doubts they can have.','ywrac'),'<b>','</b>'); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="six section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/06-icon.png" alt="icon 06" />
                    <h2><?php _e('Automatic sending of emails ','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __( 'With few and simple options, you will be able to configure the automatic sending of emails to users that have abandoned their cart on your site.%3$sAfter a certain time span since the cart was abandoned, the email will be generated automatically: in this way, there could be %1$snew invitations every day%2$s, which likely will be converted in purchase orders.','ywrac' ),'<b>','</b>','<br>' ) ?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/06.png" alt="Automatic sending email" />
            </div>
        </div>
    </div>
    <div class="seven section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/07.png" alt="WPML" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/07-icon.png" alt="icon 07" />
                    <h2><?php _e('WPML','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('Currently WPML is the most popular system among those who want a multi-language and multi-currency site. WPML compatibility symbolizes an added value to any products, and YITH WooCommerce Recover Abandoned Cart is no less so.%3$sThe plugin will let you %1$screate an email in the different languages%2$s set in your site: the system will send it in the language related to the one of the abandoned cart of the user.','ywrac'),'<b>','</b>','<br>'); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="eight section section-odd clear">
        <div class="landing-container">
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/08-icon.png" alt="icon 08" />
                    <h2><?php _e('Log','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __( 'A complete list of the traced emails of your %1$sshop%2$s to have an order history of all users that have thought to purchase on your shop.%3$sThanks to the general page, you will be able to see at a glance the complete list of all generated %1$semails%2$s from the plugin and their related information, just like the IDs of the abandoned carts and the date in which they were sent.','ywrac' ),'<b>','</b>','<br>' ) ?>
                </p>
            </div>
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/08.png" alt="Optional product" />
            </div>
        </div>
    </div>
    <div class="nine section section-even clear">
        <div class="landing-container">
            <div class="col-1">
                <img src="<?php echo YITH_YWRAC_URL?>/assets/images/09.png" alt="Report" />
            </div>
            <div class="col-2">
                <div class="section-title">
                    <img src="<?php echo YITH_YWRAC_URL?>/assets/images/09-icon.png" alt="icon 09" />
                    <h2><?php _e('Advanced report','ywrac');?></h2>
                </div>
                <p>
                    <?php echo sprintf( __('Read the information of the plugin report, so that you can easily go back to the recap data of your site. The overall amount of %1$sabandoned carts%2$s and those recovered, the %1$sconversion rate%2$s of the carts, and the total %1$searnings%2$s that they had on the economy of your shop.And for statistics lovers, the amount of emails sent, and how many visits these have generated.%3$sEverything to make your e-commerce better.','ywrac'),'<b>','</b>','<br>'); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="section section-cta section-odd">
        <div class="landing-container">
            <div class="premium-cta">
                <p>
                    <?php echo sprintf( __('Upgrade to %1$spremium version%2$s of %1$sYITH WooCommerce Recover Abandoned Cart%2$s to benefit from all features!','ywrac'),'<span class="highlight">','</span>' );?>
                </p>
                <a href="<?php echo $this->get_premium_landing_uri() ?>" target="_blank" class="premium-cta-button button btn">
                    <span class="highlight"><?php _e('UPGRADE','ywrac');?></span>
                    <span><?php _e('to the premium version','ywrac');?></span>
                </a>
            </div>
        </div>
    </div>
</div>