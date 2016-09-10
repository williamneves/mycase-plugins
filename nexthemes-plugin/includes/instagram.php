<?php


class Instagram
{
    /**
     * The API OAuth URL.
     */
    private $get_user_url = 'https://api.instagram.com/v1/users/self/?access_token=';

    private $get_user_media_url = 'https://api.instagram.com/v1/users/%1$s/media/recent/?access_token=%2$s&count=%3$s';

    /**
     * The Instagram API Key.
     *
     * @var string
     */
    private $_token;

    /**
     * The Instagram OAuth API secret.
     *
     * @var string
     */
    private $_apisecret;

    /**
     * The callback URL.
     *
     * @var string
     */
    private $_callbackurl;

    /**
     * The user access token.
     *
     * @var string
     */
    private $_accesstoken;

    /**
     * Whether a signed header should be used.
     *
     * @var bool
     */
    private $_signedheader = false;

    /**
     * Available scopes.
     *
     * @var string[]
     */

    public function __construct() {
        add_action( 'wp_ajax_instagram_validate_token', array( $this, 'instagram_validate_token' ) );
        //add_action( 'wp_ajax_nopriv_instagram_validate_token', array( $this, 'instagram_validate_token' ) );

        add_action( 'wp_ajax_nth_instagram_get_media', array( $this, 'instagram_get_media' ) );
        add_action( 'wp_ajax_nopriv_nth_instagram_get_media', array( $this, 'instagram_get_media' ) );

    }

    public function instagram_validate_token(){
        $token = $_REQUEST['token'];
        $instag_request = wp_remote_post( $this->get_user_url . $token, array('timeout' => 45));
        $contextOptions = array(
            'ssl'   => array(
                'verify_peer'   => false,
                'verify_peer_name'  => false
            )
        );
        $content = file_get_contents($this->get_user_url . $token, false, stream_context_create($contextOptions));
        echo $content;
        wp_die();
    }

    public function getInstagramMedia($user_id, $access_token, $limit){
        if(empty($user_id) || empty($access_token)) return array();

        try {
            $contextOptions = array(
                'ssl'   => array(
                    'verify_peer'   => false,
                    'verify_peer_name'  => false
                )
            );
            $_url = sprintf($this->get_user_media_url, esc_attr($user_id), esc_attr($access_token), absint($limit));
            $content = file_get_contents($_url, false, stream_context_create($contextOptions));

            return json_decode($content, true);
        } catch(Exception $e) {
            return array();
        }
    }

    public function instagram_get_media(){
        $config = get_option('nexthemes_social_settings', false);
        $limit = isset($_REQUEST['limit'])? absint($_REQUEST['limit']): 6;
        $thumbnail = isset($_REQUEST['thumbnail'])? esc_attr($_REQUEST['thumbnail']): 'low_resolution';
        $result = array();
        if($config) {
            $result = $this->getInstagramMedia($config['instagram_userid'], $config['instagram_token'], $limit);
        }

        if(!empty($result) && count($result['data'])) {
            foreach($result['data'] as $item){
                printf('<a target="_blank" class="effect_color" href="%s">', $item['link']);
                printf('<img src="%1$s" alt="instag image" />', $item['images'][$thumbnail]['url']);
                echo '</a>';
            }
        }

        wp_die();
    }

}
