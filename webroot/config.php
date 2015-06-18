<?php

/**
 * Config-file for Ami. 
 *
 * Global settings for the whole installation.
 */
date_default_timezone_set('Europe/London');

/**
 * Set the error reporting.
 *
 */
error_reporting(-1);              // Report all type of errors
ini_set('display_errors', 1);     // Display all errors 
ini_set('output_buffering', 0);   // Do not buffer outputs, write immidiately


/**
 * Define Ami paths.
 *
 */
define('AMI_INSTALL_PATH', __DIR__ . '/..');
define('AMI_THEME_PATH', AMI_INSTALL_PATH . '/theme/render.php');


/**
 * Include bootstrapping functions.
 *
 */
include(AMI_INSTALL_PATH . '/src/bootstrap.php');


/**
 * Start the session.
 *
 */
session_name(preg_replace('/[^a-z\d]/i', '', __DIR__));
session_start();


/**
 * Create the Ami variable.
 *
 */
$ami = array();


/**
 * Site wide settings.
 *
 */
$ami['lang'] = 'en';
$ami['title_append'] = ' | RM Movie Rentals';


/**
 * Theme related settings.
 *
 */
$ami['stylesheets'] = array();
$ami['stylesheets'][] = 'css/base.css';
$ami['stylesheets'][] = 'css/skeleton.css';
$ami['stylesheets'][] = 'css/layout.css';
$ami['stylesheets'][] = 'css/style.css';
$ami['stylesheets'][] = 'css/background.css';
$ami['stylesheets'][] = 'css/CCalendar.css';
$ami['stylesheets'][] = 'http://fonts.googleapis.com/css?family=Noto+Sans';


$ami['favicon'] = 'favicon.png';

/**
 * Website header.
 */
$ami['header'] = "<div class='thirteen columns alpha'><h1 id='logo'>"
        . "<span class='sitetitle'>RM Movie Rentals </span>"
        . "<span class='siteslogan'>movies at your fingertips.</span></h1></div>"
        . "<div id='topsearch' class='three columns omega'>"
        . "<form action='list.php'><input type='search' "
        . "name='search' placeholder='Search for movies'/>"
        . "</form></div>";

/**
 * Website footer.
 */
$ami['footer'] = "Copyright &copy; " . date('Y') . ", RM Movie Rentals Ltd | <a href='http://validator.w3.org/unicorn/check?ucn_uri=referer&amp;ucn_task=conformance'>Unicorn</a>";


/**
 * Settings for JavaScript.
 *
 */
$ami['modernizr'] = 'js/modernizr.js';

$ami['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js';
//$ami['jquery'] = null; // To disable jQuery

$ami['javascript_include'] = array('js/main.js');
//$ami['javascript_include'] = array('js/other.js'); // To add extra javascript files


/**
 * Google analytics.
 *
 */
$ami['google_analytics'] = null; // Set to null to disable google analytics


/**
 * Set the navigation array $menu. This is the main navigation of the site.
 */
$ami['menu'] = array(
    'home' => array('text' => 'home', 'url' => 'index.php'),
    'list' => array('text' => 'movies', 'url' => 'list.php'),
    'blog' => array('text' => 'news', 'url' => 'blog.php'),
    'calendar' => array('text' => 'this month', 'url' => 'calendar.php'),
    'about' => array('text' => 'about', 'url' => 'about.php'),
    'win' => array('text' => 'win!', 'url' => 'win.php'),
    'users' => array('text' => 'users', 'url' => 'users.php'),
);


if (!isset($_SESSION['user'])) {
    $ami['menu']['login'] = array('text' => 'login', 'url' => 'login.php');
}

if (isset($_SESSION['user'])) {
    $ami['menu']['logout'] = array('text' => 'logout', 'url' => 'logout.php');
    $ami['menu']['edit'] = array('text' => 'edit content', 'url' => 'edit.php');
    $ami['menu']['editmovies'] = array('text' => 'edit movies', 'url' => 'editmovies.php');
}

/**
 * Settings for the database.
 *
 */
define("DB_PASSWORD", "password");
define("DB_USER", "username");

$ami['database']['dsn'] = 'mysql:host=localhost;dbname=Movie;';
$ami['database']['username'] = DB_USER;
$ami['database']['password'] = DB_PASSWORD;
$ami['database']['driver_options'] = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");