<?php
namespace Wowe\Recaptcha;

use \Guzzle\Http\Client;
use \Guzzle\Common\Exception\GuzzleException;

class Recaptcha
{
    const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';
    const VERIFICATION_URL = 'https://www.google.com/recaptcha/api/siteverify';
    
    /**
     * The secret part of the API key pair from Google.
     * @var string
     */
    private $secret;

    /**
     * The site key part of the API key pair from Google.
     * @var string
     */
    private $siteKey;

    /**
     * Errors thrown from the last verification query.
     * Possible values are the error codes from the Google API and:
     * transfer-error: An exception was encountered when attempting to connect to the API.
     * api-error: An HTTP status code other than 200 was returned by the API.
     * response-error: The format of the response returned by the API could not be read.
     * @var array
     */
    private $errors = [];

    /**
     * Create a new Recaptcha instance
     * 
     * @param string $secret  The secret part of the API key pair from Google.
     * @param string $siteKey The site key part of the API key pair from Google.
     */
    public function __construct($secret, $siteKey)
    {
        $this->secret = $secret;
        $this->siteKey = $siteKey;
    }

    /**
     * Generates the script source based on the options.
     * 
     * @param  string $onload The name of a JavaScript function to be called on load.
     * @param  string $render When to render the widget ('explicit' or 'onload').
     * @param  string $hl     The language to be used for the widget.
     * @return string         The URL for the script.
     */
    private function scriptSrc($onload = null, $render = null, $hl = null)
    {
        $queryString = http_build_query(compact('onload', 'render', 'hl'));
        return self::SCRIPT_URL . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Generates the url to verify the CAPTCHA response.
     * 
     * @param  string $response The user response token.
     * @param  string $remoteIp The user's IP address
     * @return string
     */
    private function verificationUrl($response, $remoteIp = null)
    {
        $secret = $this->secret;
        return self::VERIFICATION_URL . '?' . http_build_query(compact('secret', 'response', 'remoteIp'));
    }

    /**
     * Generates a script tag based on the options.
     * 
     * @param  string $onload     The name of the JavaScirpt function to be called on load.
     * @param  string $render     When to render the widget ('explicit' or 'onload').
     * @param  string $hl         The language to be used for the widget.
     * @param  array  $attributes Additional attributes for the tag.
     * @return string
     */
    public function script($onload = null, $render = null, $hl = null, array $attributes = array())
    {
        array_push($attributes, 'async', 'defer');
        return HtmlBuilder::script($this->scriptSrc($onload, $render, $hl), null, $attributes);
    }

    /**
     * Generates a div tag for the widget based on the options.
     * 
     * @param  string $theme      The color theme of the widget ('dark' or 'light').
     * @param  string $type       The type of CAPTCHA to serve ('audio' or 'image').
     * @param  string $callback   The name of the JavaScript callback function to be executed when the user submits a successful CAPTCHA response.
     * @param  array  $attributes Additional attributes to be placed on the div.
     * @return string
     */
    public function widget($theme = null, $type = null, $callback = null, array $attributes = array())
    {
        $attributes['class'] = implode(' ', array_merge((array)(isset($attributes['class']) ? $attributes['class'] : null), ['g-recaptcha']));
        $attributes['data-sitekey'] = $this->siteKey;
        $attributes['data-theme'] = $theme;
        $attributes['data-type'] = $type;
        $attributes['data-callback'] = $callback;

        return '<div' . HtmlBuilder::attributes($attributes) . '></div>';
    }

    /**
     * Queries the Google API to determine if the CAPTCHA is valid.
     * 
     * @param  string $response The user response token.
     * @param  string $remoteIp The user's IP address
     * @return boolean
     */
    public function verify($response, $remoteIp = null)
    {
        $this->errors = [];
        try {
            $response = (new Client())->get($this->verificationUrl($response, $remoteIp))->send();
        } catch (GuzzleException $e) {
            $this->errors[] = 'transfer-error';

            return false;
        }
        
        if ($response->getStatusCode() !== 200) {
            $this->errors[] = 'api-error';

            return false;
        }

        try {
            $responseBody = $response->json();
        } catch (GuzzleException $e) {
            $this->errors[] = 'response-error';

            return false;
        }
        
        if (isset($responseBody['error-codes'])) {
            $this->errors = $responseBody['error-codes'];
        }
        return $responseBody['success'];
    }

    /**
     * The list of errors returned from the last verification query.
     * 
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}
