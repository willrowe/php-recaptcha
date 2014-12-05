<?php
namespace Wowe\Recaptcha;

class Recaptcha
{
    const SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';
    
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
     * @param  string $onload The name of a JavaScript function to be called on load.
     * @param  string $render When to render the widget ('explicit' or 'onload').
     * @param  string $hl     The language to be used for the widget.
     * @return string         The URL for the script.
     */
    private function getScriptSrc($onload = null, $render = null, $hl = null)
    {
        $queryString = http_build_query(compact('onload', 'render', 'hl'));
        return self::SCRIPT_URL . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Generates a script tag based on the options.
     * @param  string $onload     The name of the JavaScirpt function to be called on load.
     * @param  string $render     When to render the widget ('explicit' or 'onload').
     * @param  string $hl         The language ot be used for the widget.
     * @param  array  $attributes Additional attributes for the tag.
     * @return string
     */
    public function script($onload = null, $render = null, $hl = null, array $attributes = array())
    {
        return HtmlBuilder::script($this->getScriptSrc($onload, $render, $hl), null, array_merge($attributes, ['async', 'defer']));
    }

    /**
     * Generates a div tag for the widget based on the options.
     * @param  string $theme      The color theme of the widget ('dark' or 'light').
     * @param  string $type       The type of CAPTCHA to serve ('audio' or 'image').
     * @param  string $callback   The name of the JavaScript callback function to be executed when the user submits a successful CAPTCHA response.
     * @param  array  $attributes Additional attributes to be placed on the div.
     * @return string
     */
    public function widget($theme = null, $type = null, $callback = null, array $attributes = array())
    {
        $attributes['class'] = implode(' ', array_push((array)(isset($attributes['class']) ? $attributes['class'] : ''), 'g-recaptcha'));
        $attributes['data-sitekey'] = $this->siteKey;
        $attributes['data-theme'] = $theme;
        $attributes['data-type'] = $type;
        $attributes['data-callback'] = $callback;

        return '<div' . HtmlBuilder::attributes($attributes) . '></div>';
    }
}
