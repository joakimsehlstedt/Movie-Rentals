<?php

/**
 * Bootstrapping functions, essential and needed for Ami to work together with 
 * some common helpers. 
 *
 */

/**
 * Default exception handler.
 *
 */
function myExceptionHandler($exception) {
    echo "Ami: Uncaught exception: <p>" . $exception->getMessage() . "</p><pre>" .
    $exception->getTraceAsString(), "</pre>";
}

set_exception_handler('myExceptionHandler');

/**
 * Autoloader for classes.
 *
 */
function myAutoloader($class) {
    $path = AMI_INSTALL_PATH . "/src/{$class}/{$class}.php";
    if (is_file($path)) {
        include($path);
    } else {
        throw new Exception("Classfile '{$class}' does not exists.");
    }
}

spl_autoload_register('myAutoloader');

/**
 * Echo the content of an array with a html conversion for safety.
 * 
 * @param array $array as the array to be humanised.
 */
function dump($array) {
    echo "<pre>" . htmlentities(print_r($array, 1)) . "</pre>";
}

/**
 * Get current url.
 * 
 * @return string $url.
 */
function getCurrentUrl() {
    $url = "http";
    $url .= (@$_SERVER["HTTPS"] == "on") ? 's' : '';
    $url .= "://";
    $serverPort = ($_SERVER["SERVER_PORT"] == "80") ? '' :
            (($_SERVER["SERVER_PORT"] == 443 && @$_SERVER["HTTPS"] == "on") ? '' : ":{$_SERVER['SERVER_PORT']}");
    $url .= $_SERVER["SERVER_NAME"] . $serverPort . htmlspecialchars($_SERVER["REQUEST_URI"]);
    return $url;
}

/**
 * Use the current querystring as base, modify it according to $options and 
 * return the modified query string.
 *
 * @param array $options to set/change.
 * @param string $prepend this to the resulting query string
 * @return string with an updated query string.
 */
function getQueryString($options, $prepend = '?') {
    // parse query string into array
    $query = array();
    parse_str($_SERVER['QUERY_STRING'], $query);

    // Modify the existing query string with new options
    $query = array_merge($query, $options);

    // Return the modified querystring
    return $prepend . http_build_query($query, '','&amp;');
}
