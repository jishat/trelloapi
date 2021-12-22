<?php
namespace App\OAuth;

/**
 * OAuth - version of OAuth 0.1
 *
 */
class OAuth {

    private $secret;
    private $signature_method;
    private $action;
    private $nonce_chars;
    private $all_parameters;

    /**
     * Constructor
     *
     */
    function __construct($APIKey = "", $secret_key = "")
    {

        if (!empty($APIKey)) {
            $this->secret['api_key'] = $APIKey;
        }

        if (!empty($secret_key)) {
            $this->secret['secret_key'] = $secret_key;
        }

        $this->signature_method = "HMAC-SHA1";
        $this->action           = "GET";
        $this->nonce_chars      = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    }

    /**
     * Set the parameters either from a hash or a string
     *
     */
    public function setParameters($parameters = Array())
    {

        if (is_string($parameters)) {
            $parameters = $this->parseParameterString($parameters);
        }
        if (empty($this->all_parameters)) {
            $this->all_parameters = $parameters;
        } else if (!empty($parameters)) {
            $this->all_parameters = array_merge($this->all_parameters, $parameters);
        }
        if (empty($this->all_parameters['oauth_nonce'])) {
            $this->getNonce();
        }
        if (empty($this->all_parameters['oauth_timestamp'])) {
            $this->getTimeStamp();
        }
        if (empty($this->all_parameters['oauth_consumer_key'])) {
            $this->getApiKey();
        }
        if (empty($this->all_parameters['oauth_token'])) {
            $this->getAccessToken();
        }
        if (empty($this->all_parameters['oauth_signature_method'])) {
            $this->setSignatureMethod();
        }
        if (empty($this->all_parameters['oauth_version'])) {
            $this->all_parameters['oauth_version'] = "1.0";
        }

        return $this;
    }


    /**
     * Convenience method for setURL
     *
     * @param string $path
     *
     * @see setURL
     */
    public function setPath($path)
    {
        return $this->_path = $path;
    }


    /**
     * Set the signatures
     *
     */
    public function signatures($signatures)
    {
        if (!empty($signatures) && !is_array($signatures)) {
            error_log('Must pass dictionary array to OAuth.signatures');
        }
        if (!empty($signatures)) {
            if (empty($this->secret)) {
                $this->secret = Array();
            }
            $this->secret = array_merge($this->secret, $signatures);
        }
        if (isset($this->secret['api_key'])) {
            $this->secret['api_key'] = $this->secret['api_key'];
        }
        if (isset($this->secret['access_token'])) {
            $this->secret['oauth_token'] = $this->secret['access_token'];
        }
        if (isset($this->secret['access_secret'])) {
            $this->secret['oauth_secret'] = $this->secret['access_secret'];
        }
        if (isset($this->secret['access_token_secret'])) {
            $this->secret['oauth_secret'] = $this->secret['access_token_secret'];
        }
        if (empty($this->secret['api_key'])) {
            error_log('Missing required api_key in OAuth.signatures');
        }
        if (empty($this->secret['secret_key'])) {
            error_log('Missing requires secret_key in OAuth.signatures');
        }
        if (!empty($this->secret['oauth_token']) && empty($this->secret['oauth_secret'])) {
            error_log('Missing oauth_secret for supplied oauth_token in OAuth.signatures');
        }

        return $this;
    }

    public function setTokensAndSecrets($signatures)
    {
        return $this->signatures($signatures);
    }

    /**
     * Set the signature method (currently only Plaintext or SHA-MAC1)
     *
     */
    public function setSignatureMethod($method = "")
    {
        if (empty($method)) {
            $method = $this->signature_method;
        }
        $method = strtoupper($method);
        switch ($method) {
            case 'PLAINTEXT':
            case 'HMAC-SHA1':
                $this->all_parameters['oauth_signature_method'] = $method;
            break;
            default:
                error_log ("Unknown signing method $method specified for OAuthSimple.setSignatureMethod");
            break;
        }

        return $this;
    }

    /**
     * sign the request
     *
     */
    public function sign($args = array())
    {
  
        if (!empty($args['path'])) {
            $this->setPath($args['path']);
        }
        if (!empty($args['method'])) {
            $this->setSignatureMethod($args['method']);
        }
        if (!empty($args['signatures'])) {
            $this->signatures($args['signatures']);
        }
        if (empty($args['parameters'])) {
            $args['parameters'] = array();
        }
        $this->setParameters($args['parameters']);
        $normParams                           = $this->normalizedParameters();
        $this->all_parameters['oauth_signature'] = $this->generateSignature($normParams);

        return Array(
            'parameters' => $this->all_parameters,
            'signature'  => self::oauthEscape($this->all_parameters['oauth_signature']),
            'signed_url' => $this->_path . '?' . $this->normalizedParameters(),
            'header'     => $this->getHeaderString(),
            'sbs'        => $this->sbs,
        );
    }

    /**
     * Return a formatted "header" string
     * 
     */
    public function getHeaderString($args = array())
    {
        if (empty($this->all_parameters['oauth_signature'])) {
            $this->sign($args);
        }
        $result = 'OAuth ';

        foreach ($this->all_parameters as $pName => $pValue) {
            if (strpos($pName, 'oauth_') !== 0) {
                continue;
            }
            if (is_array($pValue)) {
                foreach ($pValue as $val) {
                    $result .= $pName . '="' . self::oauthEscape($val) . '", ';
                }
            } else {
                $result .= $pName . '="' . self::oauthEscape($pValue) . '", ';
            }
        }

        return preg_replace('/, $/', '', $result);
    }

    private function parseParameterString($paramString)
    {
        $elements = explode('&', $paramString);
        $result   = array();
        foreach ($elements as $element) {
            list ($key, $token) = explode('=', $element);
            if ($token) {
                $token = urldecode($token);
            }
            if (!empty($result[$key])) {
                if (!is_array($result[$key])) {
                    $result[$key] = array($result[$key], $token);
                } else {
                    $result[$key][] = $token;
                }
            } else {
                $result[$key] = $token;
            }
        }

        return $result;
    }

    private static function oauthEscape($string)
    {
        if ($string === 0) {
            return 0;
        }
        if ($string === '0') {
            return '0';
        }
        if ($string === '') {
            return '';
        }
        if (is_array($string)) {
            error_log('Array passed to oauthEscape');
        }
        $string = rawurlencode($string);

        //FIX: rawurlencode of ~
        $string = str_replace
        (
            ['%7E', '+', '!', '*', '\'', '(', ')'],
            ['~', '%20', '%21', '%2A', '%27', '%28', '%29'],
            $string
        );

        return $string;
    }

    private function getNonce($length = 5)
    {
        $result  = '';
        $cLength = strlen($this->nonce_chars);
        for ($i = 0; $i < $length; $i++) {
            $rnum   = mt_rand(0, $cLength);
            $result .= $this->nonce_chars[$rnum];
        }
        $this->all_parameters['oauth_nonce'] = $result;

        return $result;
    }

    private function getApiKey()
    {
        if (empty($this->secret['api_key'])) {
            error_log('No api_key set for OAuth');
        }
        $this->all_parameters['oauth_consumer_key'] = $this->secret['api_key'];

        return $this->all_parameters['oauth_consumer_key'];
    }

    private function getAccessToken()
    {
        if (!isset($this->secret['oauth_secret'])) {
            return '';
        }
        if (!isset($this->secret['oauth_token'])) {
            error_log('No access token (oauth_token) set for OAuth.');
        }
        $this->all_parameters['oauth_token'] = $this->secret['oauth_token'];

        return $this->all_parameters['oauth_token'];
    }

    private function getTimeStamp()
    {
        return $this->all_parameters['oauth_timestamp'] = time();
    }

    private function normalizedParameters()
    {
        $normalized_keys = array();
        $return_array    = array();

        foreach ($this->all_parameters as $paramName => $paramValue) {
            if (!preg_match('/\w+_secret/', $paramName) OR (strpos($paramValue, '@') !== 0 && !file_exists(substr($paramValue, 1)))) {
                if (is_array($paramValue)) {
                    $normalized_keys[self::oauthEscape($paramName)] = array();
                    foreach ($paramValue as $item) {
                        $normalized_keys[self::oauthEscape($paramName)][] = self::oauthEscape($item);
                    }
                } else {
                    $normalized_keys[self::oauthEscape($paramName)] = self::oauthEscape($paramValue);
                }
            }
        }

        ksort($normalized_keys);

        foreach ($normalized_keys as $key => $val) {
            if (is_array($val)) {
                sort($val);
                foreach ($val as $element) {
                    $return_array[] = $key . "=" . $element;
                }
            } else {
                $return_array[] = $key . '=' . $val;
            }

        }

        return implode("&", $return_array);
    }

    private function generateSignature()
    {
        $secretKey = '';
        if (isset($this->secret['secret_key'])) {
            $secretKey = self::oauthEscape($this->secret['secret_key']);
        }

        $secretKey .= '&';
        if (isset($this->secret['oauth_secret'])) {
            $secretKey .= self::oauthEscape($this->secret['oauth_secret']);
        }
        switch ($this->all_parameters['oauth_signature_method']) {
            case 'PLAINTEXT':
                return urlencode($secretKey);;
            case 'HMAC-SHA1':
                $this->sbs = self::oauthEscape($this->action) . '&' . self::oauthEscape($this->_path) . '&' . self::oauthEscape($this->normalizedParameters());

                return base64_encode(hash_hmac('sha1', $this->sbs, $secretKey, TRUE));
            default:
                error_log('Unknown signature method for OAuth');
            break;
        }
    }

}