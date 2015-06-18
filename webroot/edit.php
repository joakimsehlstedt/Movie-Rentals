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
$c = new CContent($options);

$html = implode($c->getHtml());

// Add content to the Ami array. -----------------------------------

$ami['main'] = "<div class='sixteen columns'><h2>Edit content</h2>" . $html . "</div>";

$ami['pageID'] = "edit";
$ami['title'] = "Edit";


// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);


