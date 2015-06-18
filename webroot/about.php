<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');


// Create CPage object
$options = array(
    'database' => $ami['database'],
    'url' => 'about',
);
$page = new CPage($options);

// Add content to the Ami array.
$ami['title'] = $page->getHtmlTitle();
$ami['main'] = "<figure class='four columns alpha'>"
        . "<br><br><br><br><br><img class='floatright' src='img.php?src=about/lego-indiana-jones.jpg"
        . "&amp;width=200&amp;save-as=jpg&amp;quality=60' alt=''/></figure>"
        . "<article class='eight columns'><h2>" . $page->getHtmlTitle() . "</h2>"
        . "<p>" . $page->getHtmlMain() . "</p></article>"
        . "<figure class='four columns omega'><br><br>"
        . "<img src='img.php?src=about/back_to_the_future.jpg"
        . "&amp;width=200&amp;save-as=jpg&amp;quality=60' alt=''/>"
        . "<img src='img.php?src=about/john-travolta.jpg"
        . "&amp;width=200&amp;save-as=jpg&amp;quality=60' alt=''/>"
        . "<img src='img.php?src=about/007.jpg"
        . "&amp;width=200&amp;save-as=jpg&amp;quality=60' alt=''/>"
        . "</figure>";


// Add content to the Ami array. -----------------------------------
$ami['pageID'] = "about";


// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
