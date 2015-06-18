<?php

/**
 * This is an Ami pagecontroller.
 * 
 */

// Include main config-file which also sets the $ami variable and it's defaults.
include (__DIR__ . '/config.php');

// How many rows to display per page
$hits = isset($_GET['hits']) && is_numeric($_GET['hits']) ? htmlentities($_GET['hits']) : 16;
// Which is the current page to display, use this to calculate the offset value
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? htmlentities($_GET['page']) : 1;
// Search variables for year and stringsearch
$year1 = isset($_GET['year1']) && is_numeric($_GET['year1']) ? htmlentities($_GET['year1']) : null;
$year2 = isset($_GET['year2']) && is_numeric($_GET['year2']) ? htmlentities($_GET['year2']) : null;
$search = isset($_GET['search']) ? htmlentities($_GET['search']) : null;
// Variable for genre search
$genre = isset($_GET['g']) && is_numeric($_GET['g']) ? htmlentities($_GET['g']) : null;
// Check parameters for sorting
$orderby = isset($_GET['orderby']) ? strtolower($_GET['orderby']) : 'title';
$order = isset($_GET['order']) ? strtolower($_GET['order']) : 'asc';
in_array($orderby, array('title', 'director', 'genre', 'length', 'year',
            'format', 'quality', 'price')) or die('Check: Not valid column.');
in_array($order, array('asc', 'desc')) or die('Check: Not valid sort order.');

// Create CDBTableView object and load it with options
$options = array(
    'database' => $ami['database'],
    'tablename' => 'VMovie',
    'hits' => $hits,
    'page' => $page,
    'year1' => $year1,
    'year2' => $year2,
    'search' => $search,
    'columnToSearch' => 'title',
    'genre' => $genre,
    'orderby' => $orderby,
    'order' => $order,
);

if (isset($_GET['list'])) {
    $tableView = new CDBTableView($options);
} else {
    $tableView = new CDBGridView($options);
}

// Get html code from object
$html = $tableView->getHtml();

// Add content to the Ami array. -----------------------------------

$ami['main'] = "<div class='sixteen columns'><div class='fourteen columns alpha'>"
        . "<h2>Available movies</h2></div>"
        . "<div class='two columns omega'>"
        . "<a href='" . getQueryString(array('list' => 1)) . "'>list</a>"
        . " or <a href='" . getQueryString(array('list' => null)) . "'>grid</a> view</div></div>"
        . $html;


$ami['pageID'] = "list";
$ami['title'] = "Movies";


// Include Ami theme-engine and let it render the page.
include(AMI_THEME_PATH);
