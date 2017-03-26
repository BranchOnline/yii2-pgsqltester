<?php

namespace branchonline\pgsqltester\utils;

/**
 * String utility functions.
 *
 * @author Roelof Ruis <roelof@branchonline.nl>
 */
class StringUtil {

    /**
     * Check whether haystack ends with needle.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The ending string to match.
     * @return bool Whether the haystack ends with needle.
     */
    public static function endsWith(string $haystack, string $needle): bool {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Check whether haystack stars with needle.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The ending string to match.
     * @return bool Whether the haystack starts with needle.
     */
    public static function startsWith(string $haystack, string $needle): bool {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

}