<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

$options = array(
    'database' => $ami['database'],
);
$c = new CContentMovies($options);

$html = implode($c->getHtml());

// Add content to the Ami array. -----------------------------------

$ami['main'] = "<div class='sixteen columns'><h2>Edit movies</h3>" . $html . "</div>";

$ami['pageID'] = "editmovies";
$ami['title'] = "Edit movies";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);

