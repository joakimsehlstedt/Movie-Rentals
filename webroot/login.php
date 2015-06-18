<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

// Get parameters for sorting
$acronym = isset($_POST['acronym']) && !empty($_POST['acronym']) ?
        htmlentities($_POST['acronym']) : null;
$password = isset($_POST['password']) && !empty($_POST['password']) ?
        htmlentities($_POST['password']) : null;

// create CUser object
$options = array(
    'acronym' => $acronym,
    'password' => $password,
    'database' => $ami['database'],
);
$user = new CUser($options);

// Redirect if login successful
!isset($_SESSION['user']) or header("Location: edit.php");

// get user status
$output = $user->UserStatusString();

// Add content to the Ami array. -----------------------------------

$ami['main'] = "<div class='eight columns'><form method='post'><h2>Login</h2>"
        . "<p><label>User: </label><input type='text' name='acronym' value=''/>"
        . "<label>Password: </label><input type='text' name='password' value=''/></p>"
        . "<input type='submit' name='login' value='Login'></form><p>" . $output . "</p></div>";


$ami['pageID'] = "login";
$ami['title'] = "Login";


// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);

