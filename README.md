Recaptcha for PHP
=========================
**Recaptcha for PHP** provides a full-featured PHP implementation of the [Google reCAPTCHA API](https://developers.google.com/recaptcha/) along with some helpful adapters for other popular packages. It specifically uses the "no CAPTCHA" version released in December 2014.

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    + [Setup](#setup)  
    + [Available Methods](#available-methods)
    + [Verification Error Codes](#verification-error-codes)
- [Adapters](#adapters)
    + [Twig](#twig)
    + [Slim Framework](#slim-framework)
- [Release Notes](#release-notes)
- [License](#license)

Installation
------------
**Via Composer**:  
`composer require wowe/recaptcha:1.*`

Configuration
-------------
Only two values are required to use the package:
- `secret` The part of the API key pair used for authentication.
- `siteKey` The part of the API key pair which uniquely identifies your site.

Both of these values must be acquired from Google. Directions on how to do so may be found [here](https://developers.google.com/recaptcha/docs/start).

Usage
-----
###Setup###
In order to use the package, create a new instance of the `Recaptcha` class, passing in the `secret` and `siteKey` values.
```php
require 'vendor/autoload.php';
use \Wowe\Recaptcha\Recaptcha;

$recaptcha = new Recaptcha('secret', 'siteKey');
```
###Available Methods###
- `script($onload = null, $render = null, $hl = null, $attributes = array())`  
    Generates a script tag based on the options.  
    + $onload `string`: The name of the JavaScript function to be called on load.
    + $render `string`: When to render the widget ('explicit' or 'onload').
    + $hl `string`: The language to be used for the widget.
    + $attributes `array`: Additional attributes for the tag.
    + Returns `string`
- `widget($theme = null, $type = null, $callback = null, $attributes = array())`  
    Generates a div tag for the widget based on the options.  
    + $theme `string`: The color theme of the widget ('dark' or 'light').
    + $type `string`: The type of CAPTCHA to serve ('audio' or 'image').
    + $callback `string`: The name of the JavaScript callback function to be executed when the user submits a successful CAPTCHA response.
    + $attributes `array`: Additional attributes to be placed on the div.
    + Returns `string`
- `verify($response, $remoteIp = null)`  
    Queries the Google API to determine if the CAPTCHA is valid.
    + $response `string`: The user response token.
    + $remoteIp `string`: The user's IP address.
    + Returns `boolean`
- `errors()`  
    The list of errors from the last verification query.
    + Returns `array`

###Verification Error Codes###
If `verify` returns false, calling `errors` will return a list of the errors encountered. These will most often consist of [error codes returned by the Google API](https://developers.google.com/recaptcha/docs/verify). In addition to these it may also return:
- `transfer-error`: An exception was encountered when attempting to connect to the API.
- `api-error`: A HTTP status code other than 200 was returned by the API.
- `response-error`: The format of the response returned by the API could not be read.

Adapters
--------
###Twig###
**[Website](http://twig.sensiolabs.org/) | [GitHub](https://github.com/twigphp/Twig)**  
The `script` and `widget` methods can be exposed in Twig templates by adding an instance of the included `TwigExtension` class to the Twig environment. The `TwigExtension` instance must be initialized with an instance of the `Recaptcha` class. `script` is mapped to a function called `recaptchaScript` and `widget` is mapped to a function called `recaptchaWidget`. All arguments are the same as the definitions [above](#available-methods).
```php
// index.php
require 'vendor/autoload.php';
use \Wowe\Recaptcha\Recaptcha;
use \Wowe\Recaptcha\Adapters\TwigExtension;

$recaptcha = new Recaptcha('secret', 'siteKey');
$loader = new Twig_Loader_Filesystem(__DIR__ . '/views');
$twig = new Twig_Environment($loader);
$twig->addExtension(new TwigExtension($recaptcha)));
echo $twig->render('index.html');

// views/index.html
<!doctype html>
<html>
    <head>
        {{ recaptchaScript() }}
    </head>
    <body>
        <form method="POST">
            {{ recaptchaWidget() }}
            <input type="submit" />
        </form>
    </body>
</html>
```
###Slim Framework###
**[Website](http://www.slimframework.com/) | [GitHub](https://github.com/codeguy/Slim)**  
The `Recaptcha` class can be registered as a singleton in the Slim container automatically by running the `SlimManager::register` method, which has three optional arguments:
- `register($registerViewExtension = false, $recaptcha = null, $appName = null)`
    Register a Recaptcha instance with the application container.
    + $registerViewExtension `boolean`: Whether or not to also register a view extension (if available).
    + $recaptcha `\Wowe\Recaptcha\Recaptcha`: The Recaptcha instance to bind to.
    + $appName `string`: The name of the application to register with.
    + Returns void

If you would like to instantiate the `Recaptcha` class yourself (in order to get the configuration values customly), you may do so and then just pass it to the `register` method. If no `Recaptcha` instance is passed then it will attempt to create one by getting the configuration values from the `Slim` app. In order to take advantage of this, set the configuration value `recaptcha` to an array with the `secret` and `siteKey` values as in the following example:
```php
require 'vendor/autoload.php';
use \Slim\Slim;
use \Wowe\Recaptcha\Adapters\SlimManager;

$app = new Slim([
    'recaptcha' => [
        'secret' => 'secret',
        'siteKey' => 'siteKey'
    ]
]);

SlimManager::register();

$app->get('/', function () use ($app) {
    return $app->render('index.html');
});

$app->post('/', function () use ($app) {
    $recaptchaResponse = $app->request->post('g-recaptcha-response');
    var_dump($app->recaptcha->verify($recaptchaResponse), $app->recaptcha->errors());
});

$app->run();
```
If a value of `true` is passed as the `registerViewExtension` and a view engine is being used which has an extension available (eg. Twig), it will register the corresponding extension with the view engine.

Release Notes
-------------
*Additional information can be found in the CHANGELOG.md file*
- v1.0.0 - Initial release

License
-------
The **Recaptcha for PHP** package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)