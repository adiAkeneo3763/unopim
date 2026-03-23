<?php

use Intervention\Image\ImageManager;
use Webkul\Core\Core;

if (! function_exists('core')) {
    /**
     * Core helper.
     *
     * @return Core
     */
    function core()
    {
        return app('core');
    }
}

if (! function_exists('array_permutation')) {
    function array_permutation($input)
    {
        $results = [];

        foreach ($input as $key => $values) {
            if (empty($values)) {
                continue;
            }

            if (empty($results)) {
                foreach ($values as $value) {
                    $results[] = [$key => $value];
                }
            } else {
                $append = [];

                foreach ($results as &$result) {
                    $result[$key] = array_shift($values);

                    $copy = $result;

                    foreach ($values as $item) {
                        $copy[$key] = $item;
                        $append[] = $copy;
                    }

                    array_unshift($values, $result[$key]);
                }

                $results = array_merge($results, $append);
            }
        }

        return $results;
    }
}

if (! function_exists('image_manager')) {
    /**
     * Get the image manager instance.
     */
    function image_manager(): ImageManager
    {
        return app('image_manager');
    }
}
