<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

$url = isset($_GET['url']) ? strip_tags($_GET['url']) : null;

if (isset($url)) {
    // Create CPage object
    $options = array(
        'database' => $ami['database'],
        'url' => $url,
    );
    $page = new CPage($options);
    
    // Add content to the Ami array.
    $ami['title'] = $page->getHtmlTitle();
    $ami['main'] = "<h3>" . $page->getHtmlTitle() . "</h3>"
            . "<p>" . $page->getHtmlMain() . "</p>";
} else {
    $ami['title'] = "Alert!";
    $ami['main'] = "<h3>Alert!</h3>URL not defined. Please add URL to your data.";
}

// Add content to the Ami array. -----------------------------------
$ami['pageID'] = "page";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);