<?php

if (!function_exists('stripMultipleSlashes')) {
    /**
     * Find and replace any series of two or more consecutive slashes.
     * Exceptions for http:// and https://.
     *
     * @param string $path
     *
     * @return string
     */
    function stripMultipleSlashes(string $path): string
    {
        return preg_replace('/(?<!:)\/{2,}/', '/', $path);
    }
}
