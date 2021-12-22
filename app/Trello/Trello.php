<?php
namespace App\Trello;

include_once($_SERVER['DOCUMENT_ROOT'].'/bootstrap.php');
use App\OAuth\OAuth;
use App\Collection\Collection;
use App\Session\Session;


class Trello {

    /**
     * Trello API Url
     */
    protected $apiUrl = 'https://api.trello.com/1';

    /**
     * Trello Auth main url
     */
    protected $mainUrl = 'https://trello.com/1';


    /**
     * Callback url
     */
    protected $callBackUrl = 'https://bibah.jishat.com/authorize';

    /**
     * Api key from Trello API
     */
    protected $api_key;

    /**
     * OAuth Secret Key
     */
    protected $secret_key;

    /**
     * Non-OAuth or OAuth token
     */
    protected $token;

    /**
     * OAuth Secret token
     */
    protected $oauth_secret;

    /**
     * php-trello version
     */
    private $version = '1.1.1';


    /**
     * __construct
     *
     * Assign value in needed property
     * 
     */
    public function __construct($api_key, $secret_key = null, $token = null, $oauth_secret = null)
    {

        // CURL is required in order for this extension to work
        if (!function_exists('curl_init')) {
            throw new \Exception('CURL is required for php-trello');
        }

        // Sessions are used to for OAuth
        if (session_id() === '' && !headers_sent()) {
            session_start();
        }

        $this->api_key      = $api_key;
        $this->secret_key   = $secret_key;
        $this->token         = $token;
        $this->oauth_secret  = $oauth_secret;
    }



    /**
     * authorize an OAuth authorization to Trello.
     *
     */
    public function authorization($return = FALSE)
    {
        $oauth = new OAuth($this->api_key, $this->secret_key);
        $options = array(
            'name'         => 'TrelloApi',
            'redirect_uri' => $this->callBackUrl,
            'expiration'   => '1day',
            'scope'        => 'read,write',
        );

        // Get a request token from Trello
        $request = $oauth->sign(array(
            'path'       => $this->mainUrl."/OAuthGetRequestToken",
            'parameters' => array(
                'oauth_callback' => $options['redirect_uri'],
            ),
        ));

        $ch = curl_init($request['signed_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);

        // We store the token_secret for later because it's needed to get a permanent one
        parse_str($result, $returned_items);
        $request_token                  = $returned_items['oauth_token'];
        $_SESSION['oauth_token_secret'] = $returned_items['oauth_token_secret'];

        // Create and process a request with all of our options for Authorization
        $request = $oauth->sign(array(
            'path'       => "$this->mainUrl/OAuthAuthorizeToken",
            'parameters' => array(
                'oauth_token' => $request_token,
                'name'        => $options['name'],
                'expiration'  => $options['expiration'],
                'scope'       => $options['scope'],
            ),
        ));

        if ($return) {
            return $request['signed_url'];
        }

        header("Location: $request[signed_url]");
        exit;
    }


    /**
     * Get Token after authorization.
     *
     */
    public function getToken(){
        $oauth = new OAuth($this->api_key, $this->secret_key);

        $signatures = array(
            'oauth_secret' => $_SESSION['oauth_token_secret'],
            'oauth_token'  => $_GET['oauth_token'],
        );

        $request = $oauth->sign(array(
            'path'       => $this->mainUrl."/OAuthGetAccessToken",
            'parameters' => array(
                'oauth_verifier' => $_GET['oauth_verifier'],
                'oauth_token'    => $_GET['oauth_token'],
            ),
            'signatures' => $signatures,
        ));

        // Initiate our request to get a permanent access token
        $ch = curl_init($request['signed_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);

        // Parse our tokens and store them
        parse_str($result, $returned_items);
        $this->token        = $returned_items['oauth_token'];
        $this->oauth_secret = $returned_items['oauth_token_secret'];
        $_SESSION['tkn']= $this->token;
        $_SESSION['oauth_tkn_secret']= $this->oauth_secret;

        // To prevent a refresh of the page from working to re-do this step, clear out the temp
        // access token.
        unset($_SESSION['oauth_token_secret']);

        return TRUE;
    }


    /**
     * Get data of organizations/Boards/
     * Cards/Lists
     * 
     */
    public function getData($url){

        $this->oauth_secret = $_SESSION['oauth_tkn_secret'];
        $this->token = $_SESSION['tkn'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, "php-trello/$this->version");
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        $headers = null;

        if ($this->isExist()) {
            $oauth = new OAuth($this->api_key, $this->secret_key);
            $oauth
                ->setTokensAndSecrets(array('access_token' => $this->token, 'access_secret' => $this->oauth_secret,))
                ->setParameters();

            $request = $oauth->sign(array('path' => $url));
            $headers = 'Authorization: ' . $request['header'];

        }else{
            return false;
            exit();
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($headers !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, is_array($headers) ? $headers : array($headers));
        }
        
        // Get the data
        $getData = curl_exec($ch);

        return json_decode($getData, true);
    }


    /**
     * POST/PUT of organizations/Boards/
     * Cards/Lists
     * 
     */
    public function action($method, $data, $url){

        $this->oauth_secret = $_SESSION['oauth_tkn_secret'];
        $this->token = $_SESSION['tkn'];

 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, "php-trello/$this->version");
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
                $data = array();
            break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
                $data = array();
            break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
            default:
                throw new \Exception('Invalid method specified');
        }

        $headers = null;        

        if ($this->isExist()) {

            $oauth = new OAuth($this->api_key, $this->secret_key);
            $oauth
                ->setTokensAndSecrets(array('access_token' => $this->token, 'access_secret' => $this->oauth_secret,))
                ->setParameters();

            $request = $oauth->sign(array('path' => $url));
            $headers = 'Authorization: ' . $request['header'];

        }else{
            return FALSE;
            exit();
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($headers !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, is_array($headers) ? $headers : array($headers));
        }
        
        // Get the data
        $getData = curl_exec($ch);

        if (!$getData) {

            // If there was a CURL error of some sort, log it and return false
            // curl_error($ch);

            return FALSE;
        }
        

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $getData = trim($getData);
        // var_dump($responseCode);
       
        if (strpos($responseCode, '2') !== 0) {

            // If we didn't get a 2xx HTTP response from Trello, log the responsebody as an error
            // $getData;

            return FALSE;
        }

        return json_decode($getData, true);
    }


    /**
     * Check is Api key, Token, Secret, 
     * Outh Secret Exist
     * 
     */
    protected function isExist()
    {
        return $this->api_key && $this->token && $this->secret_key && $this->oauth_secret;
    }

}