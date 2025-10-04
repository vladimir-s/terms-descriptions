<?php

function wp_allowed_protocols() {
    static $protocols = array();

    if ( empty( $protocols ) ) {
        $protocols = array( 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' );
    }

    return $protocols;
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        if ('' === $needle) {
            return true;
        }
        // Use mb_substr for multi-byte safe operations, especially with UTF-8 strings
        return mb_substr($haystack, 0, mb_strlen($needle)) === $needle;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $needleLength = strlen($needle);
        if ($needleLength === 0) {
            return true; // An empty string is considered to end every string
        }
        if ($needleLength > strlen($haystack)) {
            return false; // The needle cannot be longer than the haystack
        }
        return substr($haystack, -$needleLength) === $needle;
    }
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach ($arr as $key => $unused) {
            return $key;
        }
        return null;
    }
}