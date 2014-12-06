<?php
namespace Wowe\Recaptcha;

class HtmlBuilder
{
    /**
     * Escapes the passed value for use in HTML.
     * @param  string $value The value to be escaped.
     * @return string        The escaped result.
     */
    public static function escape($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Converts the array of attribute names and values into a valid HTML string.
     * 
     * @param  array  $attributes The name and values of the attributes.
     * @return string             A valid HTML attribute string.
     */
    public static function attributes(array $attributes = array())
    {
        $formattedAttributes = array();

        foreach ($attributes as $name => $value) {
            if (is_null($value)) {
                continue;
            }
            $name = is_string($name) ? $name : $value;
            $formattedAttributes[$name] = $name . '="' . self::escape($value) . '"';
        }

        return $formattedAttributes ? ' ' . implode(' ', $formattedAttributes) : '';
    }

    /**
     * Generates a script tag.
     * @param  string $src        The URL for the script.
     * @param  string $type       The type of script.
     * @param  array  $attributes Additional attributes.
     * @return string
     */
    public static function script($src, $type = 'text/javascript', array $attributes = array())
    {
        $attributes = array_merge($attributes, compact('src', 'type'));

        return '<script' . self::attributes($attributes) . '></script>' . PHP_EOL;
    }
}
