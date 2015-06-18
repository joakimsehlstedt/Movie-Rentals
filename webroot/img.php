<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

//
// Ensure error reporting is on
//
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write directly


// Define some constant values, append slash
// Use DIRECTORY_SEPARATOR to make it work on both windows and unix.
define('IMG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

$img = new CImage();
