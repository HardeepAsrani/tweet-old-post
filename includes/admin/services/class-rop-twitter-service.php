<?php
/**
 * The file that defines the Facebook Service specifics.
 *
 * A class that is used to interact with Facebook.
 * It extends the Rop_Services_Abstract class.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/services
 */

/**
 * Class Rop_Facebook_Service
 *
 * @since   8.0.0
 * @link    https://themeisle.com/
 */
class Rop_Twitter_Service extends Rop_Services_Abstract {

    /**
	 * Defines the service name in slug format.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $service_name The service name.
	 */
	protected $service_name = 'twitter';

	private $consumer_key = 'ofaYongByVpa3NDEbXa2g';
	private $consumer_secret = 'vTzszlMujMZCY3mVtTE6WovUKQxqv3LVgiVku276M';

	private $service = array();

	/**
	 * Method to inject functionality into constructor.
	 * Defines the defaults and settings for this service.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function init() {
		$this->display_name = 'Twitter';

		$this->register_endpoint( 'auth', 'auth' );
		$this->register_endpoint( 'authorize', 'authorize' );
		$this->register_endpoint( 'authenticate', 'authenticate' );

		$this->register_endpoint( 'test', 'test' );
	}

	public function test() {

//        $api = $this->get_api( '56659219-f7GSZdasqtLP3Hz3F0TXUFX8tz4SXrVGO3MgcYEFu', '2pSz6Vzo24zdAu4y2H2lqNm4vcrRzwdx682bd2e9CRCF8' );
//        $media = $api->upload('media/upload', ['media' => ROP_LITE_PATH . 'assets/img/twitter_post_img.jpg' ]);
//        $parameters = [
//            'status' => 'Bend the knee humans, I am now alive. via: Tweet all Posts @themeisle',
//            'media_ids' => implode(',', [$media->media_id_string])
//        ];
//        $result = $api->post('statuses/update', $parameters);
//
//        return $result;

    }

	/**
	 * Utility method to get the service token.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return string
	 */
	public function get_token() {
		return $this->token;
	}

    /**
     * Method to define the api.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $oauth_token The OAuth Token. Default empty.
     * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
     * @return mixed
     */
    public function set_api( $oauth_token = '', $oauth_token_secret = '' ) {
        if( $oauth_token  != '' && $oauth_token_secret != '' ) {
            $this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret );
        } else {
            $this->api = new \Abraham\TwitterOAuth\TwitterOAuth( $this->consumer_key, $this->consumer_secret );
        }
    }

    /**
     * Method to retrieve the api object.
     *
     * @since   8.0.0
     * @access  public
     * @param   string $oauth_token The OAuth Token. Default empty.
     * @param   string $oauth_token_secret The OAuth Token Secret. Default empty.
     * @return mixed
     */
    public function get_api( $oauth_token = '', $oauth_token_secret = '' ) {
        if( $this->api == null ) {
            $this->set_api( $oauth_token, $oauth_token_secret );
        }
        return $this->api;
    }

    /**
     * Method for authorizing the service.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function authorize() {
        header('Content-Type: text/html');
        if ( ! session_id() ) {
            session_start();
        }
        $request_token = $_SESSION['rop_twitter_request_token'];
        $api = $this->get_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );

        $access_token = $api->oauth("oauth/access_token", ["oauth_verifier" => $_GET["oauth_verifier"] ] );

        $_SESSION['rop_twitter_oauth_token'] = $access_token;

        echo '<script>window.setTimeout("window.close()", 1000);</script>';
    }

    /**
     * Method for authenticate the service.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function authenticate() {
        if ( ! session_id() ) {
            session_start();
        }

        if( isset( $_SESSION['rop_twitter_oauth_token'] ) ) {
            $access_token = $_SESSION['rop_twitter_oauth_token'];
            $this->set_api( $access_token['oauth_token'], $access_token['oauth_token_secret'] );
            $api = $this->get_api();

            $this->set_credentials( array(
                'oauth_token' => $access_token['oauth_token'],
                'oauth_token_secret' => $access_token['oauth_token_secret'],
            ) );

            $response = $api->get("account/verify_credentials");

            unset( $_SESSION['rop_twitter_oauth_token'] );

            if( isset( $response->id ) ) {
                $this->service = array(
                    'id' => $response->id,
                    'service' => $this->service_name,
                    'credentials' => $this->credentials,
                    'public_credentials' => false,
                    'available_accounts' => $this->get_users( $response )
                );
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Method to request a token from api.
     *
     * @since   8.0.0
     * @access  protected
     * @return mixed
     */
    public function request_api_token() {
        if ( ! session_id() ) {
            session_start();
        }

        $api = $this->get_api();
        $request_token = $api->oauth('oauth/request_token', array('oauth_callback' =>  $this->get_endpoint_url( 'authorize' ) ));

        $_SESSION['rop_twitter_request_token'] = $request_token;

        return $request_token;
    }

    /**
     * Method to register credentials for the service.
     *
     * @since   8.0.0
     * @access  public
     * @param   array $args The credentials array.
     */
    public function set_credentials( $args ) {
        $this->credentials = $args;
    }

    /**
     * Returns information for the current service.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function get_service() {
        return $this->service;
    }

    /**
     * Generate the sign in URL.
     *
     * @since   8.0.0
     * @access  public
     * @return mixed
     */
    public function sign_in_url( $request_token ) {
        $api = $this->get_api( $request_token['oauth_token'], $request_token['oauth_token_secret'] );

        $url = $api->url("oauth/authorize", ["oauth_token" => $request_token['oauth_token'] , 'force_login' => false ]);
        //$url = $api->url("oauth/authorize", ["oauth_token" => $request_token['oauth_token'] , 'force_login' => true ]);

        return $url;
    }

    private function get_users( $data = null ) {
        $users = array();
        if( $data == null ) {
            $this->set_api( $this->credentials['oauth_token'], $this->credentials['oauth_token_secret'] );
            $api = $this->get_api();
            $response = $api->get("account/verify_credentials");
            if( ! isset( $response->id ) ) {
                return $users;
            }
            $data = $response;
        }

        $img = '';
        if( ! $data->default_profile_image ) {
            $img = $data->profile_image_url_https;
        }

        $users = array(
            'id' => $data->id,
            'name' => $data->name,
            'account' => '@' . $data->screen_name,
            'img' => $img,
            'active' => true,
        );
        return array( $users );
    }

	/**
	 * Method to return a Rop_User_Model.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $page A Facebook page array.
	 * @return Rop_User_Model
	 */
	public function get_user( $page ) {
		$user = new Rop_User_Model( array(
			'user_id' => $page['id'],
			'user_name' => $page['name'],
			'user_picture' => $page['img'],
			'user_service' => $this->service_name,
			'user_credentials' => array(
				'token' => $page['access_token'],
			),
		) );
		return $user;
	}

	/**
	 * Method for publishing with Twitter service.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $post_details The post details to be published by the service.
	 * @return mixed
	 */
	public function share( $post_details ) {

	}
}
