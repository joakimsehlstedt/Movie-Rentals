<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

// create CUser object
$options = array();
$user = new CUser($options);

// Logout user
if (isset($_POST['logout']) && ($_POST['logout'] == 'Logout')) {
    $user->LogoutUser();
}

// Redirect if logout succesful
isset($_SESSION['user']) or header("Location: index.php");

// get user status
$output = $user->UserStatusString();

// Add content to the Ami array. -----------------------------------

$ami['main'] = "<div class='eight columns'><form method='post'><h2>Logout</h2>"
        . "<input type='submit' name='logout' value='Logout'></form><p>" . $output . "</p></div>";

$ami['pageID'] = "logout";
$ami['title'] = "Logout";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);

