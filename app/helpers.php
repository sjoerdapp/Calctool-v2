<?php

/**
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the Dynq project.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  Dynq
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

if (!function_exists('set_active')) {
    /**
     * Set active class if request is in path.
     *
     * @param string $path
     * @param array  $classes
     * @param string $active
     *
     * @return string
     */
    function set_active($path, array $classes = [], $active = 'active')
    {
        if (Request::is($path)) {
            $classes[] = $active;
        }

        $class = e(implode(' ', $classes));

        return empty($classes) ? '' : "class=\"{$class}\"";
    }
}

if (!function_exists('color_darken')) {
    /**
     * Darken a color.
     *
     * @param string $hex
     * @param int    $percent
     *
     * @return string
     */
    function color_darken($hex, $percent)
    {
        $hex = preg_replace('/[^0-9a-f]/i', '', $hex);
        $new_hex = '#';

        if (mb_strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }

        for ($i = 0; $i < 3; $i++) {
            $dec = hexdec(mb_substr($hex, $i * 2, 2));
            $dec = min(max(0, $dec + $dec * $percent), 255);
            $new_hex .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
        }

        return $new_hex;
    }
}

if (!function_exists('color_contrast')) {
    /**
     * Calculates colour contrast.
     *
     * https://24ways.org/2010/calculating-color-contrast/
     *
     * @param string $hexcolor
     *
     * @return string
     */
    function color_contrast($hexcolor)
    {
        $r = hexdec(mb_substr($hexcolor, 0, 2));
        $g = hexdec(mb_substr($hexcolor, 2, 2));
        $b = hexdec(mb_substr($hexcolor, 4, 2));
        $yiq = (($r * 100) + ($g * 400) + ($b * 114)) / 1000;

        return ($yiq >= 128) ? 'black' : 'white';
    }
}

if (!function_exists('array_numeric_sort')) {
    /**
     * Numerically sort an array based on a specific key.
     *
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    function array_numeric_sort(array $array = [], $key = 'order')
    {
        uasort($array, function ($a, $b) use ($key) {
            $a = array_get($a, $key, PHP_INT_MAX);
            $b = array_get($b, $key, PHP_INT_MAX);

            $default = PHP_MAJOR_VERSION < 7 ? 1 : 0;

            return $a < $b ? -1 : ($a === $b ? $default : 1);
        });

        return $array;
    }
}

if (!function_exists('is_whitelabel')) {
    /**
     * Generate a URL to a named route, which resides in a given domain.
     *
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param string $domain
     *
     * @return string
     */
    function is_whitelabel()
    {
        return mb_strtolower(str_slug($project->project_name));
    }
}

if (!function_exists('normalize')) {
    /**
     * Convert input data to uniform string
     *
     * @param string $string
     *
     * @return string
     */
    function normalize()
    {
        return mb_strtolower(trim($string));
    }
}

if (!function_exists('cachet_redirect')) {
    /**
     * Create a new redirect response to a named route, which resides in a given domain.
     *
     * @param string $name
     * @param array  $parameters
     * @param int    $status
     * @param array  $headers
     * @param string $method
     * @param string $domain
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    function cachet_redirect($name, $parameters = [], $status = 302, $headers = [], $method = 'get', $domain = 'core')
    {
        $url = cachet_route($name, $parameters, $method, $domain);

        return app('redirect')->to($url, $status, $headers);
    }
}

if (!function_exists('get_php_classes')) {
    /**
     * Create a new redirect response to a named route, which resides in a given domain.
     *
     * @param string $code
     *
     * @return array
     */
    function get_php_classes($code)
    {
        $classes = [];
        $tokens = token_get_all($code);
        $count = count($tokens);

        for ($i = 2; $i < $count; $i++)
        {
            if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)
            {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes;
    }
}
