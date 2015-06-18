<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

$slug = isset($_GET['slug']) ? strip_tags($_GET['slug']) : null;

// Create CBlog object
$options = array(
    'database' => $ami['database'],
    'slug' => $slug,
);
$page = new CBlog($options);

// Add content to the Ami array. -----------------------------------

$disclaimer = <<<EOD
Disclaimer: RM Movie Rentals Ltd takes no responsibility for the content or accuracy of the above 
news articles, Tweets, or blog posts. This content is published for the 
entertainment of our users only. The news articles, Tweets, and blog posts 
do not represent our opinions nor can we guarantee that the reporting therein 
is completely factual.
EOD;

$ami['title'] = "Latest News";
$ami['main'] = "<article class='sixteen columns'><h2>{$ami['title']}</h2>" 
    . $page->getHtml() . "</article><article class='sixteen columns disclaimer'><br>"
        . "{$disclaimer}</article>";


$ami['pageID'] = "blog";

// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
