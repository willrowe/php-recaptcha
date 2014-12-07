<?php
namespace Wowe\Recaptcha\Adapters;

use \Wowe\Recaptcha\Recaptcha;
use \Slim\Slim;

final class SlimManager
{
    /**
     * Register a Recaptcha instance with the application container.
     * 
     * @param  boolean                    $registerViewExtension Whether or not to also register a parser extension (if available).
     * @param  \Wowe\Recaptcha\Recaptcha  $recaptcha             The Recaptcha instance to bind to.
     * @param  string                     $appName               The name of the application to register with.
     * @return void
     */
    public static function register($registerViewExtension = false, Recaptcha $recaptcha = null, $appName = null)
    {
        $app = call_user_func_array(['\Slim\Slim', 'getInstance'], array_filter([$appName]));

        $recaptcha = self::registerSingleton($app, $recaptcha);

        if ($registerViewExtension) {
            self::registerViewExtension($app, $recaptcha);
        }
    }

    /**
     * Register the singleton with the application container.
     * 
     * @param  \Slim\Slim                $app       The application instance to register with.
     * @param  \Wowe\Recaptcha\Recaptcha $recaptcha The Recaptcha instance to bind to.
     * @return \Wowe\Recaptcha\Recaptcha
     */
    private static function registerSingleton(Slim $app, Recaptcha $recaptcha = null)
    {
        if (is_null($recaptcha)) {
            $config = $app->config('recaptcha');
            $recaptcha = new Recaptcha($config['secret'], $config['siteKey']);
        }

        $app->container->singleton('recaptcha', function () use ($recaptcha) {
            return $recaptcha;
        });

        return $recaptcha;
    }

    /**
     * Register the available parser extension.
     * 
     * @param  \Slim\Slim                $app       The application instance to register with.
     * @param  \Wowe\Recaptcha\Recaptcha $recaptcha The Recaptcha instance to inject into the extension.
     * @return void
     */
    private static function registerViewExtension(Slim $app, Recaptcha $recaptcha)
    {
        $view = $app->view;
        if (!property_exists($view, 'parserExtensions')) {
            return;
        }

        $originalExtensions = $view->parserExtensions ?: [];
        $class = explode('\\', get_class($view));
        switch(end($class)) {
            case 'Twig':
                $view->parserExtensions = array_merge($originalExtensions, [new TwigExtension($recaptcha)]);
                break;
        }
    }

    private function __construct()
    {
    }
}
