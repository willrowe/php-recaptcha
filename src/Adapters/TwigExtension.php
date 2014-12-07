<?php
namespace Wowe\Recaptcha\Adapters;

use \Wowe\Recaptcha\Recaptcha;

class TwigExtension extends \Twig_Extension
{
    private $recaptcha;

    /**
     * Create a new instance of TwigExtension with the recaptcha engine.
     * 
     * @param \Wowe\Recaptcha\Recaptcha $recaptcha An instance of the Recaptcha class.
     */
    public function __construct(Recaptcha $recaptcha)
    {
        $this->recaptcha = $recaptcha;
    }

    public function getName()
    {
        return 'wowe/recaptcha';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('recaptchaScript', [$this->recaptcha, 'script'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('recaptchaWidget', [$this->recaptcha, 'widget'], ['is_safe' => ['html']])
        ];
    }
}
